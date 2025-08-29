<?php

namespace App\Controller;
use App\Repository\TicketRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;




class ExportController extends AbstractController
{
    #[Route('/export/excel', name: 'app_export_excel')]
    public function exportExcel(TicketRepository $repo): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Colonnes
       
        $sheet->setCellValue('B1', 'Lieu');
        $sheet->setCellValue('C1', 'Statut');
        $sheet->setCellValue('D1', 'Date');

        $interventions = $repo->findAll();
        $row = 2;

        foreach ($interventions as $i) {
           
            $sheet->setCellValue('B' . $row, $i->getLocalisation());
            $sheet->setCellValue('C' . $row, $i->getStatut());
            $sheet->setCellValue('D' . $row, $i->getDate()->format('Y-m-d'));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $filename = "interventions.xlsx";
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition','attachment;filename="'. $filename .'"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }

    #[Route('/export/pdf', name: 'app_export_pdf')]
    public function exportPdf(TicketRepository $repo): Response
    {
        $interventions = $repo->findAll();

        // Configuration Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        // Créer le HTML pour le PDF
        $html = '<h1 style="text-align:center;">Liste des Interventions</h1>';
        $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">
                    <thead>
                        <tr style="background-color:#f2f2f2;">
                            
                            <th>Lieu</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($interventions as $i) {
            $html .= '<tr>
                       
                        <td>'. $i->getLocalisation() .'</td>
                        <td>'. $i->getStatut() .'</td>
                        <td>'. $i->getDate()->format('Y-m-d') .'</td>
                      </tr>';
        }

        $html .= '</tbody></table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF comme réponse
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="interventions.pdf"'
            ]
        );
    }
}
