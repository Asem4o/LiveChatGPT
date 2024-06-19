<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExcelService;

class ExcelController extends AbstractController
{
    private $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    #[Route('/excel', name: 'excel', methods: ['GET'])]
    public function index(): Response
    {
        $filePath = 'C:\xampp\htdocs\public\test.xlsx'; // Adjust the file path
        $excelData = $this->excelService->readExcelFile($filePath);

        return $this->render('excel/index.html.twig', [
            'data' => $excelData,
        ]);
    }

}
