<?php

namespace App\Controller\Front;

use App\Entity\Covoiturage;
use App\Entity\Participant;
use App\Entity\User;
use App\Form\CovoiturageType;
use App\Repository\CovoiturageRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/covoiturage')]
class CovoiturageController extends AbstractController
{
    #[Route('/', name: 'app_covoiturage')]
    public function index(
        Request $request,
        CovoiturageRepository $covoiturageRepository,
        ParticipantRepository $participantRepository
    ): Response
    {
        $filters = [
            'depart' => trim((string) $request->query->get('depart', '')),
            'destination' => trim((string) $request->query->get('destination', '')),
        ];

        $trips = $covoiturageRepository->search(
            $filters['depart'] !== '' ? $filters['depart'] : null,
            $filters['destination'] !== '' ? $filters['destination'] : null
        );

        $tripIds = array_map(static fn (Covoiturage $trip): ?int => $trip->getId(), $trips);
        $tripIds = array_values(array_filter($tripIds, static fn (?int $id): bool => $id !== null));

        $participantsCountByTrip = $participantRepository->getCountsByTripIds($tripIds);
        $isBookedByTrip = [];
        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            $bookedTripIds = $participantRepository->getBookedTripIdsForUser($currentUser, $tripIds);
            $isBookedByTrip = array_fill_keys($bookedTripIds, true);
        }

        return $this->render('front/covoiturage/index.html.twig', [
            'trips' => $trips,
            'filters' => $filters,
            'participantsCountByTrip' => $participantsCountByTrip,
            'isBookedByTrip' => $isBookedByTrip,
        ]);
    }

    #[Route('/ajouter', name: 'app_covoiturage_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trip = new Covoiturage();
        $form = $this->createForm(CovoiturageType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('Utilisateur non authentifie.');
            }

            $trip->setConducteur($user);
            $entityManager->persist($trip);
            $entityManager->flush();

            $this->addFlash('success', 'Trajet ajoute avec succes.');

            return $this->redirectToRoute('app_covoiturage');
        }

        return $this->render('front/covoiturage/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/reserver', name: 'app_covoiturage_book', methods: ['POST'])]
    public function book(
        Covoiturage $trip,
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('book-covoiturage' . $trip->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Requete invalide.');

            return $this->redirectToRoute('app_covoiturage');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        if ($trip->getConducteur()?->getId() === $user->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas reserver votre propre covoiturage.');

            return $this->redirectToRoute('app_covoiturage');
        }

        if ($participantRepository->isUserParticipant($user, $trip)) {
            $this->addFlash('warning', 'Vous avez deja reserve ce covoiturage.');

            return $this->redirectToRoute('app_covoiturage');
        }

        $currentCount = $participantRepository->countParticipantsByTrip($trip);
        if ($currentCount >= (int) $trip->getPlaces()) {
            $this->addFlash('error', 'Ce covoiturage est complet.');

            return $this->redirectToRoute('app_covoiturage');
        }

        $participant = new Participant();
        $participant->setPassager($user);
        $participant->setCovoiturage($trip);

        $entityManager->persist($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Reservation enregistree avec succes.');

        return $this->redirectToRoute('app_covoiturage');
    }

    #[Route('/{id}/annuler', name: 'app_covoiturage_cancel', methods: ['POST'])]
    public function cancel(
        Covoiturage $trip,
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('cancel-covoiturage' . $trip->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Requete invalide.');

            return $this->redirectToRoute('app_covoiturage');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifie.');
        }

        $participant = $participantRepository->findOneByUserAndTrip($user, $trip);
        if (!$participant instanceof Participant) {
            $this->addFlash('warning', 'Aucune reservation a annuler pour ce trajet.');

            return $this->redirectToRoute('app_covoiturage');
        }

        $entityManager->remove($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Reservation annulee avec succes.');

        return $this->redirectToRoute('app_covoiturage');
    }
}
