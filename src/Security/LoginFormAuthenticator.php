<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AiRiskScoringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private const RISK_FLAGGED_THRESHOLD = 5;
    private const RISK_FACE_REQUIRED_THRESHOLD = 8;
    private const RISK_SUSPEND_THRESHOLD = 12;
    private const RISK_BAN_THRESHOLD = 20;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private AiRiskScoringService $aiRiskScoringService
    ) {}

    /**
     * 🔐 AUTHENTIFICATION
     */
    public function authenticate(Request $request): Passport
    {
        $email = trim((string) $request->request->get('_username', ''));
        $password = (string) $request->request->get('_password', '');

        if ($email === '' || $password === '') {
            throw new CustomUserMessageAuthenticationException('Email et mot de passe requis.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new CustomUserMessageAuthenticationException('Email invalide.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user instanceof User) {
            if ($user->getSuspiciousActivityScore() >= self::RISK_BAN_THRESHOLD && !$user->isBanned()) {
                $user->ban();
                $this->entityManager->flush();
            } elseif ($user->getSuspiciousActivityScore() >= self::RISK_SUSPEND_THRESHOLD && !$user->isSuspended() && !$user->isBanned()) {
                $user->suspend((new \DateTime())->modify('+30 minutes'));
                $this->entityManager->flush();
            }

            if ($user->isDeactivatedByUser()) {
                $request->getSession()->set('reactivation_email', $email);
                throw new CustomUserMessageAuthenticationException('Compte desactive. Envoyez une demande de reactivation a l\'administrateur.');
            }

            if ($user->isBanned()) {
                $request->getSession()->set('reactivation_email', $email);
                throw new CustomUserMessageAuthenticationException('Votre compte est banni. Envoyez une demande a l\'administrateur.');
            }

            if ($user->isSuspended()) {
                throw new CustomUserMessageAuthenticationException('Votre compte est suspendu temporairement.');
            }

            if ($user->getSuspiciousActivityScore() >= self::RISK_FACE_REQUIRED_THRESHOLD) {
                throw new CustomUserMessageAuthenticationException(
                    'Connexion par mot de passe bloquee temporairement. Utilisez la reconnaissance faciale.'
                );
            }
        }

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    /**
     * ✅ REDIRECTION APRÈS LOGIN (US-04)
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $this->removeTargetPath($request->getSession(), $firewallName);
        $request->getSession()->remove('reactivation_email');

        $user = $token->getUser();
        if ($user instanceof User) {
            $ipAddress = $request->getClientIp();
            $user->recordSuccessfulLogin($ipAddress);
            $risk = $this->aiRiskScoringService->scoreLoginRisk($user, $ipAddress, true);
            $this->applyAiRiskOutcome($user, $risk);
            $this->applySecurityThresholds($user);
            $this->entityManager->flush();
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_dashboard')
            );
        }

        if (in_array('ROLE_HOST', $roles, true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('host_dashboard')
            );
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('app_profile')
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $email = trim((string) $request->request->get('_username', ''));
        if ($email !== '') {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if ($user instanceof User) {
                $user->recordFailedLogin();
                $risk = $this->aiRiskScoringService->scoreLoginRisk($user, $request->getClientIp(), false);
                $this->applyAiRiskOutcome($user, $risk);
                $this->applySecurityThresholds($user);
                $this->entityManager->flush();
            }
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    /**
     * @param array{success: bool, riskScore: float, suspicious: bool, reasons: list<string>, error: ?string} $risk
     */
    private function applyAiRiskOutcome(User $user, array $risk): void
    {
        if (!$risk['success']) {
            return;
        }

        $riskScore = $risk['riskScore'];
        if ($risk['suspicious']) {
            $penalty = $riskScore >= 0.90 ? 4 : ($riskScore >= 0.80 ? 3 : 2);
            $user->increaseSuspiciousActivity($penalty);
        } elseif ($riskScore < 0.20) {
            $user->decreaseSuspiciousActivity(1);
        }

        if (
            $risk['suspicious']
            && $this->aiRiskScoringService->isAutoSuspendEnabled()
            && $riskScore >= $this->aiRiskScoringService->getAutoSuspendThreshold()
            && !$user->isBanned()
            && !$user->isSuspended()
        ) {
            $user->suspend((new \DateTime())->modify('+30 minutes'));
        }
    }

    private function applySecurityThresholds(User $user): void
    {
        $score = $user->getSuspiciousActivityScore();

        if ($score >= self::RISK_BAN_THRESHOLD) {
            if (!$user->isBanned()) {
                $user->ban();
            }
            return;
        }

        if ($score >= self::RISK_SUSPEND_THRESHOLD) {
            if (!$user->isSuspended() && !$user->isBanned()) {
                $user->suspend((new \DateTime())->modify('+30 minutes'));
            }
            return;
        }

        if ($score >= self::RISK_FLAGGED_THRESHOLD) {
            // Threshold used by admin views to surface suspicious accounts.
            return;
        }
    }
}
