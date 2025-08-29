<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(): Response
    {
        return $this->render('ticket/index.html.twig');
    }

    #[Route('/employe/dashboard', name: 'employe_dashboard')]
    public function employeDashboard(): Response
    {
        return $this->render('ticket/new.html.twig');
    }
}
