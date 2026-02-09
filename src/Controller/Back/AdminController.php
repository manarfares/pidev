<?php

namespace App\Controller\Back;

use App\Entity\Covoiturage;
use App\Entity\User;
use App\Form\CovoiturageType;
use App\Repository\CovoiturageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Dashboard overview
     */
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // Fetch statistics from your services
        $stats = [
            'totalUsers' => 2847,
            'totalHosts' => 456,
            'totalGuests' => 2391,
            'totalServices' => 1234,
            'totalTools' => 892,
            'totalBookings' => 5678,
            'totalToolRentals' => 3421,
            'pendingReports' => 23,
            'flaggedAccounts' => 12,
            'monthlyRevenue' => 45680,
            'cancellationRate' => 4.2,
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * Users management
     */
    #[Route('/users', name: 'admin_users')]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('search');
        $role = $request->query->get('role');
        $status = $request->query->get('status');
        $sort = $request->query->get('sort', 'newest');

        $users = $userRepository->findForAdmin($search, $role, $status, $sort);
        $stats = $userRepository->getAdminStats();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'active_users_count' => $stats['active'],
            'inactive_users_count' => $stats['inactive'],
            'total_users_count' => $stats['total'],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'sort' => $sort,
            ],
        ]);
    }

    #[Route('/users/stats', name: 'admin_users_stats')]
    public function usersStats(UserRepository $userRepository): Response
    {
        return $this->render('admin/users_stats.html.twig', [
            'stats' => $userRepository->getAdminStats(),
        ]);
    }

    #[Route('/users/export', name: 'admin_users_export')]
    public function exportUsers(Request $request, UserRepository $userRepository): StreamedResponse
    {
        $search = $request->query->get('search');
        $role = $request->query->get('role');
        $status = $request->query->get('status');
        $sort = $request->query->get('sort', 'newest');
        $users = $userRepository->findForAdmin($search, $role, $status, $sort);

        $response = new StreamedResponse(function () use ($users): void {
            $handle = fopen('php://output', 'wb');
            if (!$handle) {
                return;
            }

            fputcsv($handle, ['ID', 'Email', 'Role', 'Status'], ';');
            foreach ($users as $user) {
                $roleLabel = 'Voyageur';
                if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                    $roleLabel = 'Administrateur';
                } elseif (in_array('ROLE_HOST', $user->getRoles(), true)) {
                    $roleLabel = 'Hote';
                }

                fputcsv($handle, [
                    $user->getId(),
                    $user->getEmail(),
                    $roleLabel,
                    $user->isVerified() ? 'Actif' : 'Inactif',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="users-export.csv"');

        return $response;
    }

    /**
     * Toggle user status (suspend/reactivate)
     */
    #[Route('/users/{id}/toggle-status', name: 'admin_user_toggle_status', methods: ['POST'])]
    public function toggleUserStatus(string $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($this->isCsrfTokenValid('toggle-status' . $id, $request->request->get('_token'))) {
            $user = $userRepository->find($id);
            if ($user instanceof User) {
                $user->setIsVerified(!$user->isVerified());
                $entityManager->flush();
                $this->addFlash('success', 'Le statut de l\'utilisateur a ete mis a jour.');
            }
        }

        return $this->redirectToRoute('admin_users');
    }

    /**
     * Covoiturages management
     */
    #[Route('/covoiturages', name: 'admin_covoiturages')]
    public function covoiturages(Request $request, CovoiturageRepository $covoiturageRepository): Response
    {
        $filters = [
            'search' => trim((string) $request->query->get('search', '')),
            'depart' => trim((string) $request->query->get('depart', '')),
            'destination' => trim((string) $request->query->get('destination', '')),
            'sort' => (string) $request->query->get('sort', 'date_desc'),
        ];

        $covoiturages = $covoiturageRepository->findForAdmin(
            $filters['search'] !== '' ? $filters['search'] : null,
            $filters['depart'] !== '' ? $filters['depart'] : null,
            $filters['destination'] !== '' ? $filters['destination'] : null,
            $filters['sort']
        );

        return $this->render('admin/covoiturages.html.twig', [
            'covoiturages' => $covoiturages,
            'total_covoiturages_count' => $covoiturageRepository->count([]),
            'filters' => $filters,
        ]);
    }

    #[Route('/covoiturages/stats', name: 'admin_covoiturages_stats')]
    public function covoituragesStats(CovoiturageRepository $covoiturageRepository): Response
    {
        return $this->render('admin/covoiturages_stats.html.twig', [
            'stats' => $covoiturageRepository->getStatsByCity(),
        ]);
    }

    #[Route('/covoiturages/export', name: 'admin_covoiturages_export')]
    public function exportCovoiturages(Request $request, CovoiturageRepository $covoiturageRepository): StreamedResponse
    {
        $search = trim((string) $request->query->get('search', ''));
        $depart = trim((string) $request->query->get('depart', ''));
        $destination = trim((string) $request->query->get('destination', ''));
        $sort = (string) $request->query->get('sort', 'date_desc');

        $covoiturages = $covoiturageRepository->findForAdmin(
            $search !== '' ? $search : null,
            $depart !== '' ? $depart : null,
            $destination !== '' ? $destination : null,
            $sort
        );

        $response = new StreamedResponse(function () use ($covoiturages): void {
            $handle = fopen('php://output', 'wb');
            if (!$handle) {
                return;
            }

            fputcsv($handle, ['ID', 'Depart', 'Destination', 'Date depart', 'Places', 'Conducteur'], ';');
            foreach ($covoiturages as $trip) {
                fputcsv($handle, [
                    $trip->getId(),
                    $trip->getDepart(),
                    $trip->getDestination(),
                    $trip->getDateDepart()?->format('Y-m-d H:i:s'),
                    $trip->getPlaces(),
                    $trip->getConducteur()?->getEmail(),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="covoiturages-export.csv"');

        return $response;
    }

    #[Route('/covoiturages/{id}/edit', name: 'admin_covoiturage_edit', methods: ['GET', 'POST'])]
    public function editCovoiturage(Covoiturage $covoiturage, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CovoiturageType::class, $covoiturage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le covoiturage a ete modifie.');

            return $this->redirectToRoute('admin_covoiturages');
        }

        return $this->render('admin/covoiturage_edit.html.twig', [
            'covoiturage' => $covoiturage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/covoiturages/{id}/delete', name: 'admin_covoiturage_delete', methods: ['POST'])]
    public function deleteCovoiturage(Covoiturage $covoiturage, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-covoiturage' . $covoiturage->getId(), $request->request->get('_token'))) {
            $entityManager->remove($covoiturage);
            $entityManager->flush();
            $this->addFlash('success', 'Le covoiturage a ete supprime.');
        }

        return $this->redirectToRoute('admin_covoiturages');
    }

    /**
     * Services moderation
     */
    #[Route('/services', name: 'admin_services')]
    public function services(Request $request): Response
    {
        $tab = $request->query->get('tab', 'all');
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        // Fetch services from your repository
        // $services = $this->serviceRepository->findByFilters($tab, $search, $status);

        return $this->render('admin/services.html.twig', [
            // 'services' => $services,
            'pending_count' => 1,
            'reported_count' => 1,
        ]);
    }

    /**
     * Approve a service
     */
    #[Route('/services/{id}/approve', name: 'admin_service_approve', methods: ['POST'])]
    public function approveService(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('approve' . $id, $request->request->get('_token'))) {
            // Approve service logic here
            $this->addFlash('success', 'Le service a été approuvé.');
        }

        return $this->redirectToRoute('admin_services');
    }

    /**
     * Hide a service
     */
    #[Route('/services/{id}/hide', name: 'admin_service_hide', methods: ['POST'])]
    public function hideService(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('hide' . $id, $request->request->get('_token'))) {
            // Hide service logic here
            $this->addFlash('success', 'Le service a été masqué.');
        }

        return $this->redirectToRoute('admin_services');
    }

    /**
     * Suspend a service
     */
    #[Route('/services/{id}/suspend', name: 'admin_service_suspend', methods: ['POST'])]
    public function suspendService(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('suspend' . $id, $request->request->get('_token'))) {
            // Suspend service logic here
            $this->addFlash('success', 'Le service a été suspendu.');
        }

        return $this->redirectToRoute('admin_services');
    }

    /**
     * Tools moderation
     */
    #[Route('/tools', name: 'admin_tools')]
    public function tools(Request $request): Response
    {
        $tab = $request->query->get('tab', 'all');
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        return $this->render('admin/tools.html.twig', [
            'maintenance_count' => 1,
            'reported_count' => 1,
        ]);
    }

    /**
     * Activate a tool
     */
    #[Route('/tools/{id}/activate', name: 'admin_tool_activate', methods: ['POST'])]
    public function activateTool(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('activate' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Le matériel a été réactivé.');
        }

        return $this->redirectToRoute('admin_tools');
    }

    /**
     * Hide a tool
     */
    #[Route('/tools/{id}/hide', name: 'admin_tool_hide', methods: ['POST'])]
    public function hideTool(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('hide' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Le matériel a été masqué.');
        }

        return $this->redirectToRoute('admin_tools');
    }

    /**
     * Suspend a tool
     */
    #[Route('/tools/{id}/suspend', name: 'admin_tool_suspend', methods: ['POST'])]
    public function suspendTool(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('suspend' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Le matériel a été suspendu.');
        }

        return $this->redirectToRoute('admin_tools');
    }

    /**
     * Bookings oversight
     */
    #[Route('/bookings', name: 'admin_bookings')]
    public function bookings(Request $request): Response
    {
        $tab = $request->query->get('tab', 'all');
        $search = $request->query->get('search');
        $type = $request->query->get('type');
        $status = $request->query->get('status');

        return $this->render('admin/bookings.html.twig', [
            'service_bookings_count' => 4,
            'tool_rentals_count' => 2,
            'cancelled_count' => 1,
        ]);
    }

    /**
     * Reports and fraud monitoring
     */
    #[Route('/reports', name: 'admin_reports')]
    public function reports(Request $request): Response
    {
        $tab = $request->query->get('tab', 'reports');
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        return $this->render('admin/reports.html.twig', [
            'pending_reports_count' => 3,
            'critical_alerts_count' => 2,
            'unread_alerts_count' => 3,
        ]);
    }

    /**
     * Resolve a report
     */
    #[Route('/reports/{id}/resolve', name: 'admin_report_resolve', methods: ['POST'])]
    public function resolveReport(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('resolve' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Le signalement a été résolu.');
        }

        return $this->redirectToRoute('admin_reports');
    }

    /**
     * Dismiss a report
     */
    #[Route('/reports/{id}/dismiss', name: 'admin_report_dismiss', methods: ['POST'])]
    public function dismissReport(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('dismiss' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Le signalement a été rejeté.');
        }

        return $this->redirectToRoute('admin_reports');
    }

    /**
     * Mark alert as read
     */
    #[Route('/alerts/{id}/mark-read', name: 'admin_alert_mark_read', methods: ['POST'])]
    public function markAlertRead(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('mark-read' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'L\'alerte a été marquée comme lue.');
        }

        return $this->redirectToRoute('admin_reports');
    }

    /**
     * Analytics
     */
    #[Route('/analytics', name: 'admin_analytics')]
    public function analytics(Request $request): Response
    {
        $range = $request->query->get('range', '30d');

        return $this->render('admin/analytics.html.twig', [
            'range' => $range,
        ]);
    }

    /**
     * Settings
     */
    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }

    /**
     * Update profile settings
     */
    #[Route('/settings/profile', name: 'admin_settings_profile', methods: ['POST'])]
    public function updateProfile(Request $request): Response
    {
        if ($this->isCsrfTokenValid('settings-profile', $request->request->get('_token'))) {
            $this->addFlash('success', 'Votre profil a été mis à jour.');
        }

        return $this->redirectToRoute('admin_settings');
    }

    /**
     * Update notification settings
     */
    #[Route('/settings/notifications', name: 'admin_settings_notifications', methods: ['POST'])]
    public function updateNotifications(Request $request): Response
    {
        if ($this->isCsrfTokenValid('settings-notifications', $request->request->get('_token'))) {
            $this->addFlash('success', 'Vos préférences de notification ont été mises à jour.');
        }

        return $this->redirectToRoute('admin_settings');
    }

    /**
     * Update password
     */
    #[Route('/settings/password', name: 'admin_settings_password', methods: ['POST'])]
    public function updatePassword(Request $request): Response
    {
        if ($this->isCsrfTokenValid('settings-password', $request->request->get('_token'))) {
            $this->addFlash('success', 'Votre mot de passe a été mis à jour.');
        }

        return $this->redirectToRoute('admin_settings');
    }

    /**
     * Update security settings
     */
    #[Route('/settings/security', name: 'admin_settings_security', methods: ['POST'])]
    public function updateSecurity(Request $request): Response
    {
        if ($this->isCsrfTokenValid('settings-security', $request->request->get('_token'))) {
            $this->addFlash('success', 'Vos paramètres de sécurité ont été mis à jour.');
        }

        return $this->redirectToRoute('admin_settings');
    }

    /**
     * Update platform settings
     */
    #[Route('/settings/platform', name: 'admin_settings_platform', methods: ['POST'])]
    public function updatePlatform(Request $request): Response
    {
        if ($this->isCsrfTokenValid('settings-platform', $request->request->get('_token'))) {
            $this->addFlash('success', 'Les paramètres de la plateforme ont été mis à jour.');
        }

        return $this->redirectToRoute('admin_settings');
    }

    /**
     * Show user details
     */
    #[Route('/users/{id}', name: 'admin_user_show')]
    public function showUser(string $id): Response
    {
        return $this->redirectToRoute('admin_users');
    }
}
