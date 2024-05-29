<?php

namespace App\Controller\Excel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\SmartWH\Arrivals\ItemsCCP;
use App\Entity\SmartWH\Arrivals\RecordCCP;
use App\Entity\Utilities\SAPInterface\Material;
use App\Form\SmartWH\Arrivals\InsertExcellType;
use App\Repository\SmartWH\Arrivals\ItemsCCPRepository as ArrivalsItemsCCPRepository;
use App\Repository\SmartWH\Arrivals\RecordCCPRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpKernel\KernelInterface;

class CreatorExcelController extends AbstractController
{
    private $recordCCPRepository;
    private $doctrine;
    private $kernel;
    private $itemsCCPRepository;

    
    public function __construct(RecordCCPRepository $recordCCPRepository, ManagerRegistry $doctrine, KernelInterface $kernel, ArrivalsItemsCCPRepository $itemsCCPRepository)
    {
        $this->doctrine = $doctrine;
        $this->recordCCPRepository = $recordCCPRepository;
        $this->kernel = $kernel;
        $this->itemsCCPRepository = $itemsCCPRepository;
    }

    public function index(RecordCCPRepository $recordCCPRepository, Request $request, ArrivalsItemsCCPRepository $itemsCCPRepository)
    {
        $Folios = $recordCCPRepository->findAll();
       
        return $this->render('SmartWH/Arrivals/Reports/Ccpinbound.html.twig', 
        [          
            'folios' => $Folios,
        ]);
    }


    function insertDataFromExcel(Request $request)
    {
        $refresh = false;
        $view = null;
        $title = null ;
        
            
        $title = "Ingresa Un Archivo Excel";
        $em = $this->doctrine->getManager();

        $form = $this->createForm(InsertExcellType::class, null, array(
        'action' => $this->generateUrl('generate_excel'),
        'method' => 'POST'
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            if (strtoupper($form->get('excelFile')->getData()->getClientOriginalExtension())== 'XLSX') 
            {
               
                $excelFile= $form->get('excelFile')->getData();
                $originalName = $excelFile->getPathname();

                if ($originalName != NULL) 
                {
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($originalName);
                    $worksheet = $spreadsheet->getActiveSheet();

                    $headers = array();
                    $headersCount=0;
                    $headersEnd = false;
                    $expectedHeaders = ["No Parte", "Fecha", "Guia(s)", "Cantidad", "Clave Embalaje", "Descripcion Embalaje", "Total Individual", "Peso Total",  "Fraccion",  "No Pedimento", "Regimen Pedimento"];

                    while ($headersEnd == false)
                    {
                        if ($worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue() !="") 
                        {
                            $headers[$headersCount + 1] = $worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue();
                            $headersCount ++;
                        }else
                        {
                            $headersEnd = true;
                        }
                    }

                    $allExpectedHeadersPresent = true;
                    foreach ($expectedHeaders as $header) 
                    {
                        if (!in_array($header, $headers)) 
                        {
                            $allExpectedHeadersPresent = false;
                        }
                    }



                    if ($allExpectedHeadersPresent)
                    {   ///////////////////////////////////////////codigo existente
                        $recordCCP = new RecordCCP();
                        $recordCCP->setCreatedby($this->getUser());
                        $recordCCP->setDatecreation(new \DateTime());
                        $em->persist($recordCCP);
                        $em->flush();
                        $row =2;
                        $highestDataRow = $worksheet->getHighestDataRow();


                        while ($worksheet->getCellByColumnAndRow(1, $row)->getValue() !='' ||  $row <= $highestDataRow) 
                        {
                            foreach ($headers as $index => $headerName) 
                            {
                                if ($headerName == "No. Parte")
                                {
                                $data[$row - 1]["Material"] = ltrim($worksheet->getCellByColumnAndRow($index, $row)->getValue(), "0");
                                }
                                else 
                                {
                                    // Se ajusta el código para manejar celdas con fórmulas
                                    $cellValue = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                    if (strpos($cellValue, "=")!== false)
                                    {
                                    // La celda contiene una fórmula, obtenemos el valor original
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    } 
                                    else 
                                    {
                                    // La celda no contiene una fórmula, usamos el valor directamente
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    }
                                    // $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                }
                            }
                            $row ++;
                        }


                        foreach($data as $record)
                        {
                            $numParte  = in_array("No Parte", $headers) ? $record["No Parte"] : "0";
                            if (in_array("Fecha", $headers)) 
                            {
                                $dateExcel = DateTime::createFromFormat('d/m/Y', $record["Fecha"]);
                                if ($dateExcel === false) 
                                {
                                    // Si la conversión falla, maneja el error como prefieras
                                    $dateExcel = new \DateTime(); //valor predeterminado
                                }
                            } 
                            else 
                            {
                                $dateExcel = " "; // O maneja el caso de error como prefieras
                            }

                    
                            $guideNum =  in_array("Guia(s)", $headers) ? $record["Guia(s)"] :"";
                            $qty = in_array("Cantidad", $headers) ? $record["Cantidad"] :"";
                            $keyPack = in_array("Clave Embalaje", $headers) ? $record["Clave Embalaje"] :"";
                            $descripPack = in_array("Descripcion Embalaje", $headers) ? $record["Descripcion Embalaje"] :"";
                            $individualVal = in_array("Total Individual", $headers) ? $record["Total Individual"] : ""; 
                            $totalWeight = in_array("Peso Total", $headers) ? intval($record["Peso Total"]) :"";   
                            $tariffFraction = in_array("Fraccion", $headers) ? $record["Fraccion"] :"";    
                            $petition = in_array( "No Pedimento", $headers) ? $record["No Pedimento"] :"Dato no encontrado";  
                            $clavePedimento =  in_array("Regimen Pedimento", $headers) ? $record["Regimen Pedimento"] : $record["Regimen Pedimento"];    

                            $Itemccp = new ItemsCCP();
        
                            if( $Itemccp->setNumPharman($numParte) == "0" ||  $Itemccp->setNumPharman($numParte) == null ||  $Itemccp->setNumPharman($numParte) == "" ){
                                $Itemccp->setNumPharman("NUM NO IDENTIFICADO");
                            }
                            $Itemccp->setNumPharman($numParte);
                            $Itemccp->setDateExcell($dateExcel);
                            $Itemccp->setShippingReference($guideNum);
                            $Itemccp->setQuantity($qty);
                            $Itemccp->setPackagingKey($keyPack);
                            $Itemccp->setDescriptionPackaging($descripPack);
                            $Itemccp->setIndividualValue($individualVal);
                            $Itemccp->setTotalWeight($totalWeight);
                            $Itemccp->setTariffFraction($tariffFraction);
                            $Itemccp->setPedimento($petition);
                            $Itemccp->setClavePedimento($clavePedimento);
                            $recordCCP->addFolioexcel($Itemccp);
                            $em->persist($Itemccp);
                            // $em->persist($itemsCCP);
                            $em->flush();
                        }

                        $materialsNotidentified = $em->getRepository(ItemsCCP::class)
                        ->createQueryBuilder('p')
                        ->where (
                        "p.keyProductSAP = ''  
                        OR p.materialDescriptionSAP = ''
                        OR p.keyUnitMessureSAT = ''
                        OR p.DescripUnitMessureSAT = '' 
                        OR p.dangerousMaterial = ''
                        OR p.packagingKey = ''
                        OR p.descriptionPackaging = ''
                        OR p.unitWeightSAT =''
                        OR p.countrySAT =''
                        OR
                        p.keyProductSAP IS NULL
                        OR p.materialDescriptionSAP IS NULL
                        OR p.keyUnitMessureSAT IS NULL
                        OR p.DescripUnitMessureSAT IS NULL
                        OR p.packagingKey IS NULL
                        OR p.descriptionPackaging IS NULL
                        OR p.dangerousMaterial IS NULL
                        OR p.unitWeightSAT IS NULL
                        OR p.countrySAT IS NULL"
                        )
                        ->getQuery()
                        ->getResult();


                        foreach ($materialsNotidentified as $item) 
                        { 
                            // Itera sobre la lista que se ha consultado e identificado
                            $material = $em->getRepository(Material::class)->findOneBy(['materialNumber' => $item->getNumPharman()]);
                            if ($material!== null) 
                            {
                                $item->setMaterialDescriptionSAP($material->getMaterialDescription()); //STRING 
                                $item->setKeyProductSAP($material->getClaveProductoBienesSAT()); //STRING 32111500
                                $item->setKeyUnitMessureSAT($material->getClaveUnidadMedidaSAT()); //STRING  H87
                                $item->setDescripUnitMessureSAT($material->getDescripcionUnidadMedidaSAT()); // Pieza
                                $item->setDangerousMaterial($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING 
                                $item->setPackagingKey($material->getClaveTipoEmbalajeMaterialPeligrosoSAT()); //STRING
                                $item->setDescriptionPackaging($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING
                                $item->setUnitWeightSAT($material->getUnidadDePeso()); //string
                                $item->setcountrySAT($material->getCountryOrigin()); //string
                                // Persist changes 
                                $em->persist($item);
                            }
                        }
                        $em->flush();
                        $refresh =true;

                            ////////////////////////codigo existenteeeeee
                    }
                    else
                    {
                        $headersString = implode(", ", $headers); 
                        if($headers ==='' || $headers ==null  ||  $headers == '0')
                        {
                            $headersString="ARCHIVO INVALIDO, NO CONTIENE NOMBRES EN LAS COLUMNAS.";
                            $form->addError(new FormError($headersString));
                        }
                        else
                        {
                            $form->addError(new FormError('Los nombres de columnas son invalidos. Nombres de columnas actuales: '.$headersString.' (COMPÁRALOS CONTRA EL EJEMPLO DE LAS INSTRUCCIONES)'));
                        }  
                    }
                }           
            } 
            else
            {
            $form->addError(new FormError('Archivo de Excel Invalido (Formato de archivo actual.'. $form->get('excelFile')->getData()->getClientOriginalExtension(). "). "));
            }
        }
        
        $view = $this->renderView('SmartWH/Arrivals/Reports/InsertExcel.html.twig', array(
            'form' => $form->createView(),
        ));
        $response = new Response(json_encode(array('content'=>$view,'title'=> $title, 'refresh' => $refresh ))); 
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }

    function complementExcelMaterials ($folio, Request $request)
    {
        $em = $this->doctrine->getManager();
        $refresh = false;
        $view = null;
        $title = null ;

        $title = "Ingresa Un Archivo Excel";

        $form = $this->createForm(InsertExcellType::class, null, array(
        'action' => $this->generateUrl('complement_excel', ['folio'=>$folio]),
        'method' => 'POST'
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            if (strtoupper($form->get('excelFile')->getData()->getClientOriginalExtension())== 'XLSX') 
            {
                

               
                $excelFile= $form->get('excelFile')->getData();
                $originalName = $excelFile->getPathname();

                if ($originalName != NULL) 
                {
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($originalName);
                    $worksheet = $spreadsheet->getActiveSheet();

                    $headers = array();
                    $headersCount=0;
                    $headersEnd = false;
                    $expectedHeaders = ["No Parte", "Fecha", "Guia(s)", "Cantidad", "Clave Embalaje", "Descripcion Embalaje", "Total Individual", "Peso Total",  "Fraccion",  "No Pedimento", "Regimen Pedimento"];

                    while ($headersEnd == false)
                    {
                        if ($worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue() !="") 
                        {
                            $headers[$headersCount + 1] = $worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue();
                            $headersCount ++;
                        }else
                        {
                            $headersEnd = true;
                        }
                    }

                    $allExpectedHeadersPresent = true;
                    foreach ($expectedHeaders as $header) 
                    {
                        if (!in_array($header, $headers)) 
                        {
                            $allExpectedHeadersPresent = false;
                        }
                    }

                    if ($allExpectedHeadersPresent)
                    {   ///////////////////////////////////////////codigo existente
                        $recordCCP = $em->getRepository(RecordCCP::class)->find($folio); 
                        $row =2;
                        $highestDataRow = $worksheet->getHighestDataRow();

                        while ($worksheet->getCellByColumnAndRow(1, $row)->getValue() !=''  ||  $row <= $highestDataRow) 
                        {
                            foreach ($headers as $index => $headerName) 
                            {
                                if ($headerName == "No. Parte")
                                {
                                $data[$row - 1]["Material"] = ltrim($worksheet->getCellByColumnAndRow($index, $row)->getValue(), "0");
                                }
                                else 
                                {
                                    // Se ajusta el código para manejar celdas con fórmulas
                                    $cellValue = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                    if (strpos($cellValue, "=")!== false)
                                    {
                                    // La celda contiene una fórmula, obtenemos el valor original
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    } 
                                    else 
                                    {
                                    // La celda no contiene una fórmula, usamos el valor directamente
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    }
                                    // $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                }
                            }
                            $row ++;
                        }


                        foreach($data as $record)
                        {
                            $numParte  = isset($record["No Parte"])? $record["No Parte"] : "";
                            if (in_array("Fecha", $headers)) 
                            {
                                $dateExcel = DateTime::createFromFormat('d/m/Y', $record["Fecha"]);
                                if ($dateExcel === false) 
                                {
                                    // Si la conversión falla, maneja el error como prefieras
                                    $dateExcel = new \DateTime(); //valor predeterminado
                                }
                            } 
                            else 
                            {
                                $dateExcel = " "; // O maneja el caso de error como prefieras
                            }

                    
                            $guideNum =  in_array("Guia(s)", $headers) ? $record["Guia(s)"] :"";
                            $qty = in_array("Cantidad", $headers) ? $record["Cantidad"] :"";
                            $keyPack = in_array("Clave Embalaje", $headers) ? $record["Clave Embalaje"] :"";
                            $descripPack = in_array("Descripcion Embalaje", $headers) ? $record["Descripcion Embalaje"] :"";
                            $individualVal = in_array("Total Individual", $headers) ? $record["Total Individual"] : ""; 
                            $totalWeight = in_array("Peso Total", $headers) ? intval($record["Peso Total"]) :"";   
                            $tariffFraction = in_array("Fraccion", $headers) ? $record["Fraccion"] :"";    
                            $petition = in_array( "No Pedimento", $headers) ? $record["No Pedimento"] :"Dato no encontrado";  
                            $clavePedimento =  in_array("Regimen Pedimento", $headers) ? $record["Regimen Pedimento"] : $record["Regimen Pedimento"];    

                            $Itemccp = new ItemsCCP();
        
                            $Itemccp->setNumPharman($numParte);
                            $Itemccp->setDateExcell($dateExcel);
                            $Itemccp->setShippingReference($guideNum);
                            $Itemccp->setQuantity($qty);
                            $Itemccp->setPackagingKey($keyPack);
                            $Itemccp->setDescriptionPackaging($descripPack);
                            $Itemccp->setIndividualValue($individualVal);
                            $Itemccp->setTotalWeight($totalWeight);
                            $Itemccp->setTariffFraction($tariffFraction);
                            $Itemccp->setPedimento($petition);
                            $Itemccp->setClavePedimento($clavePedimento);
                            $recordCCP->addFolioexcel($Itemccp);
                            $em->persist($Itemccp);
                            // $em->persist($itemsCCP);
                            $em->flush();
                        }

                        $materialsNotidentified = $em->getRepository(ItemsCCP::class)
                        ->createQueryBuilder('p')
                        ->where (
                        "p.keyProductSAP = ''  
                        OR p.materialDescriptionSAP = ''
                        OR p.keyUnitMessureSAT = ''
                        OR p.DescripUnitMessureSAT = '' 
                        OR p.dangerousMaterial = ''
                        OR p.packagingKey = ''
                        OR p.descriptionPackaging = ''
                        OR p.unitWeightSAT =''
                        OR p.countrySAT =''
                        OR
                        p.keyProductSAP IS NULL
                        OR p.materialDescriptionSAP IS NULL
                        OR p.keyUnitMessureSAT IS NULL
                        OR p.DescripUnitMessureSAT IS NULL
                        OR p.packagingKey IS NULL
                        OR p.descriptionPackaging IS NULL
                        OR p.dangerousMaterial IS NULL
                        OR p.unitWeightSAT IS NULL
                        OR p.countrySAT IS NULL"
                        )
                        ->getQuery()
                        ->getResult();


                        foreach ($materialsNotidentified as $item) 
                        { 
                            // Itera sobre la lista que se ha consultado e identificado
                            $material = $em->getRepository(Material::class)->findOneBy(['materialNumber' => $item->getNumPharman()]);
                            if ($material!== null) 
                            {
                                $item->setMaterialDescriptionSAP($material->getMaterialDescription()); //STRING 
                                $item->setKeyProductSAP($material->getClaveProductoBienesSAT()); //STRING 32111500
                                $item->setKeyUnitMessureSAT($material->getClaveUnidadMedidaSAT()); //STRING  H87
                                $item->setDescripUnitMessureSAT($material->getDescripcionUnidadMedidaSAT()); // Pieza
                                $item->setDangerousMaterial($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING 
                                $item->setPackagingKey($material->getClaveTipoEmbalajeMaterialPeligrosoSAT()); //STRING
                                $item->setDescriptionPackaging($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING
                                $item->setUnitWeightSAT($material->getUnidadDePeso()); //string
                                $item->setcountrySAT($material->getCountryOrigin()); //string
                                // Persist changes 
                                $em->persist($item);
                            }
                        }
                        $em->flush();
                        $refresh =true;

                            ////////////////////////codigo existenteeeeee
                    }
                    else
                    {
                        $headersString = implode(", ", $headers); 
                        if($headers ==='' || $headers ==null  ||  $headers == '0')
                        {
                            $headersString="ARCHIVO INVALIDO, NO CONTIENE NOMBRES EN LAS COLUMNAS.";
                            $form->addError(new FormError($headersString));
                        }
                        else
                        {
                            $form->addError(new FormError('Los nombres de columnas son invalidos. Nombres de columnas actuales: '.$headersString.' (COMPÁRALOS CONTRA EL EJEMPLO DE LAS INSTRUCCIONES)'));
                        }  
                    }
                }           
            } 
            else
            {
            $form->addError(new FormError('Archivo de Excel Invalido (Formato de archivo actual.'. $form->get('excelFile')->getData()->getClientOriginalExtension(). "). "));
            }
        }

        $view = $this->renderView('SmartWH/Arrivals/Reports/InsertExcel.html.twig', array(
        'form' => $form->createView(),
        ));

        $response = new Response(json_encode(array('content'=>$view,'title'=> $title, 'refresh' => $refresh ))); 
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }


    function deleteAndUpdate($folio, Request $request)
    {
        $refresh = false;
        $view = null;
        $title = null ;

        $title = "Ingresa Un Archivo Excel";

        $form = $this->createForm(InsertExcellType::class, null, array(
        'action' => $this->generateUrl('remove_and_update', ['folio'=>$folio]),
        'method' => 'POST'
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            if (strtoupper($form->get('excelFile')->getData()->getClientOriginalExtension())== 'XLSX') 
            {
                

               
                $excelFile= $form->get('excelFile')->getData();
                $originalName = $excelFile->getPathname();

                if ($originalName != NULL) 
                {
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($originalName);
                    $worksheet = $spreadsheet->getActiveSheet();

                    $headers = array();
                    $headersCount=0;
                    $headersEnd = false;
                    $expectedHeaders = ["No Parte", "Fecha", "Guia(s)", "Cantidad", "Clave Embalaje", "Descripcion Embalaje", "Total Individual", "Peso Total",  "Fraccion",  "No Pedimento", "Regimen Pedimento"];

                    while ($headersEnd == false)
                    {
                        if ($worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue() !="") 
                        {
                            $headers[$headersCount + 1] = $worksheet->getCellByColumnAndRow($headersCount + 1, 1)->getValue();
                            $headersCount ++;
                        }else
                        {
                            $headersEnd = true;
                        }
                    }

                    $allExpectedHeadersPresent = true;
                    foreach ($expectedHeaders as $header) 
                    {
                        if (!in_array($header, $headers)) 
                        {
                            $allExpectedHeadersPresent = false;
                        }
                    }

                    if ($allExpectedHeadersPresent)
                    {   ///////////////////////////////////////////codigo existente
                        
                        $em = $this->doctrine->getManager();
                        $repository = $em->getRepository(ItemsCCP::class);

                        // Buscar todos los objetos ItemsCCP que coincidan con el criterio
                        $itemsToDelete = $repository->findBy(['recordccp' => $folio]);

                        foreach ($itemsToDelete as $item) 
                        {
                        $em->remove($item);
                        }

                        $em->flush();
                        $em->clear();
                        $recordCCP = $em->getRepository(RecordCCP::class)->find($folio); 
                        $row =2;
                        $highestDataRow = $worksheet->getHighestDataRow();

                        while ($worksheet->getCellByColumnAndRow(1, $row)->getValue() !='' ||  $row <= $highestDataRow) 
                        {
                            foreach ($headers as $index => $headerName) 
                            {
                                if ($headerName == "No. Parte")
                                {
                                $data[$row - 1]["Material"] = ltrim($worksheet->getCellByColumnAndRow($index, $row)->getValue(), "0");
                                }
                                else 
                                {
                                    // Se ajusta el código para manejar celdas con fórmulas
                                    $cellValue = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                    if (strpos($cellValue, "=")!== false)
                                    {
                                    // La celda contiene una fórmula, obtenemos el valor original
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    } 
                                    else 
                                    {
                                    // La celda no contiene una fórmula, usamos el valor directamente
                                    $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getCalculatedValue();
                                    }
                                    // $data[$row - 1][$headerName] = $worksheet->getCellByColumnAndRow($index, $row)->getValue();
                                }
                            }
                            $row ++;
                        }


                        foreach($data as $record)
                        {
                            $numParte  = in_array("No Parte", $headers) ? $record["No Parte"] : "";
                            if (in_array("Fecha", $headers)) 
                            {
                                $dateExcel = DateTime::createFromFormat('d/m/Y', $record["Fecha"]);
                                if ($dateExcel === false) 
                                {
                                    // Si la conversión falla, maneja el error como prefieras
                                    $dateExcel = new \DateTime(); //valor predeterminado
                                }
                            } 
                            else 
                            {
                                $dateExcel = " "; // O maneja el caso de error como prefieras
                            }

                    
                            $guideNum =  in_array("Guia(s)", $headers) ? $record["Guia(s)"] :"";
                            $qty = in_array("Cantidad", $headers) ? $record["Cantidad"] :"";
                            $keyPack = in_array("Clave Embalaje", $headers) ? $record["Clave Embalaje"] :"";
                            $descripPack = in_array("Descripcion Embalaje", $headers) ? $record["Descripcion Embalaje"] :"";
                            $individualVal = in_array("Total Individual", $headers) ? $record["Total Individual"] : ""; 
                            $totalWeight = in_array("Peso Total", $headers) ? intval($record["Peso Total"]) :"";   
                            $tariffFraction = in_array("Fraccion", $headers) ? $record["Fraccion"] :"";    
                            $petition = in_array( "No Pedimento", $headers) ? $record["No Pedimento"] :"Dato no encontrado";  
                            $clavePedimento =  in_array("Regimen Pedimento", $headers) ? $record["Regimen Pedimento"] : $record["Regimen Pedimento"];    

                            $Itemccp = new ItemsCCP();
        
                            $Itemccp->setNumPharman($numParte);
                            $Itemccp->setDateExcell($dateExcel);
                            $Itemccp->setShippingReference($guideNum);
                            $Itemccp->setQuantity($qty);
                            $Itemccp->setPackagingKey($keyPack);
                            $Itemccp->setDescriptionPackaging($descripPack);
                            $Itemccp->setIndividualValue($individualVal);
                            $Itemccp->setTotalWeight($totalWeight);
                            $Itemccp->setTariffFraction($tariffFraction);
                            $Itemccp->setPedimento($petition);
                            $Itemccp->setClavePedimento($clavePedimento);
                            $recordCCP->addFolioexcel($Itemccp);
                            $em->persist($Itemccp);
                            // $em->persist($itemsCCP);
                            $em->flush();
                        }

                        $materialsNotidentified = $em->getRepository(ItemsCCP::class)
                        ->createQueryBuilder('p')
                        ->where (
                        "p.keyProductSAP = ''  
                        OR p.materialDescriptionSAP = ''
                        OR p.keyUnitMessureSAT = ''
                        OR p.DescripUnitMessureSAT = '' 
                        OR p.dangerousMaterial = ''
                        OR p.packagingKey = ''
                        OR p.descriptionPackaging = ''
                        OR p.unitWeightSAT =''
                        OR p.countrySAT =''
                        OR
                        p.keyProductSAP IS NULL
                        OR p.materialDescriptionSAP IS NULL
                        OR p.keyUnitMessureSAT IS NULL
                        OR p.DescripUnitMessureSAT IS NULL
                        OR p.packagingKey IS NULL
                        OR p.descriptionPackaging IS NULL
                        OR p.dangerousMaterial IS NULL
                        OR p.unitWeightSAT IS NULL
                        OR p.countrySAT IS NULL"
                        )
                        ->getQuery()
                        ->getResult();


                        foreach ($materialsNotidentified as $item) 
                        { 
                            // Itera sobre la lista que se ha consultado e identificado
                            $material = $em->getRepository(Material::class)->findOneBy(['materialNumber' => $item->getNumPharman()]);
                            if ($material!== null) 
                            {
                                $item->setMaterialDescriptionSAP($material->getMaterialDescription()); //STRING 
                                $item->setKeyProductSAP($material->getClaveProductoBienesSAT()); //STRING 32111500
                                $item->setKeyUnitMessureSAT($material->getClaveUnidadMedidaSAT()); //STRING  H87
                                $item->setDescripUnitMessureSAT($material->getDescripcionUnidadMedidaSAT()); // Pieza
                                $item->setDangerousMaterial($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING 
                                $item->setPackagingKey($material->getClaveTipoEmbalajeMaterialPeligrosoSAT()); //STRING
                                $item->setDescriptionPackaging($material->getDescripcionEmbalajeDelMaterialPeligrosoSAT()); // STRING
                                $item->setUnitWeightSAT($material->getUnidadDePeso()); //string
                                $item->setcountrySAT($material->getCountryOrigin()); //string
                                // Persist changes 
                                $em->persist($item);
                            }
                        }
                        $em->flush();
                        $refresh =true;

                            ////////////////////////codigo existenteeeeee
                    }
                    else
                    {
                        $headersString = implode(", ", $headers); 
                        if($headers ==='' || $headers ==null  ||  $headers == '0')
                        {
                            $headersString="ARCHIVO INVALIDO, NO CONTIENE NOMBRES EN LAS COLUMNAS.";
                            $form->addError(new FormError($headersString));
                        }
                        else
                        {
                            $form->addError(new FormError('Los nombres de columnas son invalidos. Nombres de columnas actuales: '.$headersString.' (COMPÁRALOS CONTRA EL EJEMPLO DE LAS INSTRUCCIONES)'));
                        }  
                    }
                }           
            } 
            else
            {
            $form->addError(new FormError('Archivo de Excel Invalido (Formato de archivo actual.'. $form->get('excelFile')->getData()->getClientOriginalExtension(). "). "));
            }
        }


        $view = $this->renderView('SmartWH/Arrivals/Reports/InsertExcel.html.twig', array(
        'form' => $form->createView(),
        ));

        $response = new Response(json_encode(array('content'=>$view,'title'=> $title, 'refresh' => $refresh ))); 
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }






    function showItemsAction($ccpid)
    {
        $em = $this->doctrine->getManager();
        $vinculedFolio = $em ->getRepository(ItemsCCP::class)->findby(["recordccp"=>$ccpid]);

        $view = null;
        $title = null ;
        $refresh = false;
                
        $title = "Lista de Materiales Por Folio";
        $view = $this->renderView('SmartWH/Arrivals/Reports/ShowItemsCCP.html.twig', array(
        'items' => $vinculedFolio,
        'folio' => $ccpid
        ));
                
        $response = new Response(json_encode(array('content'=>$view,'title'=> $title, 'refresh' => $refresh ))); 
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }

    
    public function additItem($recordccp)
    {
        $em = $this->doctrine->getManager();
        $count =9;

        $itemsPrint = $em->getRepository(ItemsCCP::class)
        ->createQueryBuilder('p')
        ->where('p.recordccp =:recordccp')->setParameter('recordccp', $recordccp)
        //->andWhere('p.clavePedimento  =:clavePedimento')->setParameter('clavePedimento', $clavepedimento)
        ->getQuery()
        ->getResult();


        $inputFile = $this->kernel->getProjectDir() . '/public/bundles/ShippingMonitor/CCP/CCP_INBOUND_LAYOUT.xlsx';
    
        // Read template 
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFile); // Reemplaza esto con la ruta a tu archivo de plantilla
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);


        // Config columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
        $sheet->getColumnDimension('S')->setAutoSize(true);
        $sheet->getColumnDimension('T')->setAutoSize(true);
        $sheet->getColumnDimension('U')->setAutoSize(true);
        $sheet->getColumnDimension('V')->setAutoSize(true);
        $sheet->getColumnDimension('W')->setAutoSize(true);
        $sheet->getColumnDimension('X')->setAutoSize(true);
        $sheet->getColumnDimension('Y')->setAutoSize(true);
        $sheet->getColumnDimension('Z')->setAutoSize(true);
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        


        $contM=1;
        foreach ($itemsPrint as $item)
        {
            //

            $sheet->setCellValue('A'. ($count ), $contM);
            if($item->getNumPharman()==null || ($item->getNumPharman() =="" || ($item->getNumPharman()== 0))){
                $sheet->setCellValue('B'. ($count ), "NUM MATERIAL FALTANTE");
                $sheet->setCellValue('C'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue('E'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue('H'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue('I'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue('J'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("K". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("O". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("P". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("Q". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("R". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("S". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("T". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("U". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("V". ($count ), "FALTO NUM MATERIAL");   
                $sheet->setCellValue("W". ($count ), "FALTO NUM MATERIAL");                      
                $sheet->setCellValue("Y". ($count ), "FALTO NUM MATERIAL");   
                $sheet->setCellValue("Z". ($count ), "FALTO NUM MATERIAL");  
                $sheet->setCellValue("AA". ($count ),"FALTO NUM MATERIAL"); 
                $sheet->setCellValue("X". ($count ) ,"FALTO NUM MATERIAL"); 
                $sheet->setCellValue("AB". ($count), "FALTO NUM MATERIAL");
                $sheet->setCellValue('F'. ($count ),"FALTO NUM MATERIAL");
                $sheet->setCellValue('G'. ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("L". ($count ), "FALTO NUM MATERIAL");
                $sheet->setCellValue("M". ($count ),"FALTO NUM MATERIAL" );
                $sheet->setCellValue("N". ($count ),"FALTO NUM MATERIAL");

            }
            else{
            $sheet->setCellValue('B'. ($count ), $item->getNumPharman());
            $sheet->setCellValue('C'. ($count ), $item->getDateExcell());
            $sheet->setCellValue('D'. ($count ), "");
            $sheet->setCellValue('E'. ($count ), $item->getShippingReference());
            $sheet->setCellValue('F'. ($count ), $item->getKeyProductSAP());
            $sheet->setCellValue('G'. ($count ), $item->getMaterialDescriptionSAP());
            $sheet->setCellValue('H'. ($count ), $item->getQuantity());
            $sheet->setCellValue('I'. ($count ), $item->getKeyUnitMessureSAT());
            $sheet->setCellValue('J'. ($count ), "N/A");
            $sheet->setCellValue("K". ($count ),  "NO");
            $sheet->setCellValue("L". ($count ), $item->getdangerousMaterial());
            $sheet->setCellValue("M". ($count ), $item->getpackagingKey());
            $sheet->setCellValue("N". ($count ), $item->getdescriptionPackaging());
            $sheet->setCellValue("O". ($count ), $item->getTotalWeight());
            $sheet->setCellValue("P". ($count ), $item->getTotalWeight());
            $sheet->setCellValue("Q". ($count ), $item->getUnitWeightSAT());
            $sheet->setCellValue("R". ($count ), $item->getTariffFraction());
            $sheet->setCellValue("S". ($count ), $item->getTotalValue());
            $sheet->setCellValue("T". ($count ), "USD");
            $sheet->setCellValue("U". ($count ), "SI");
            $clavePedimento =$item->getClavePedimento();
            if($item->getCountrySAT()  != null || $item->getCountrySAT() !="" ){
               $sheet->setCellValue("X". ($count ), $item->getCountrySAT());   
            }
            else
            {
                $sheet->setCellValue("X". ($count ), "N/A");   
            }
            if ($clavePedimento === 'A1') {
                $sheet->setCellValue("V". ($count ), "IMD");   
                $sheet->setCellValue("W". ($count ), "ENTRADA");                      
                     
                $sheet->setCellValue("Y". ($count ), "01");   
                $sheet->setCellValue("Z". ($count ), "05");  
                $sheet->setCellValue("AA". ($count ), "01"); 
            } else if ( "in" != $clavePedimento) {
                $sheet->setCellValue("V". ($count), "ITE");   
                $sheet->setCellValue("W". ($count ), "ENTRADA");                      
                $sheet->setCellValue("Y". ($count ), "01");   
                $sheet->setCellValue("Z". ($count ), "04");  
                $sheet->setCellValue("AA". ($count ), "18"); 
            
                if ($clavePedimento === 'AF') {
                $sheet->setCellValue("V". ($count ), "ITR");   
                $sheet->setCellValue("W". ($count ), "ENTRADA");                      
                $sheet->setCellValue("Y". ($count ), "01");   
                $sheet->setCellValue("Z". ($count ), "05");  
                $sheet->setCellValue("AA". ($count ), "01"); 
                } else if ($clavePedimento == NULL) {            
                $sheet->setCellValue("V". ($count ), "SIN CLAVE PEDIMENTO");   
                $sheet->setCellValue("W". ($count ), "SIN CLAVE PEDIMENTO");                      
                $sheet->setCellValue("Y". ($count ), "SIN CLAVE PEDIMENTO");   
                $sheet->setCellValue("Z". ($count ), "SIN CLAVE PEDIMENTO");  
                $sheet->setCellValue("AA". ($count ), "SIN CLAVE PEDIMENTO");      
                }
            }
            $sheet->setCellValue("AB". ($count), $item->getPedimento());
        }
            // Incrementa el contador para mover abajo en la hoja de trabajo
            $count++;
            $contM++;
       
        }

        $folioNumber = $recordccp;
        $baseName = "CCP_INBOUND_FOLIO:";
        $finalName = str_replace(".xlsx", "", $baseName). $folioNumber. ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$finalName\""); 
        header('Cache-Control: max-age=0');

        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

    }


    public function deleteFoliosByCriteria($folio)
    {
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(ItemsCCP::class);

        // Buscar todos los objetos ItemsCCP que coincidan con el criterio
        $itemsToDelete = $repository->findBy(['recordccp' => $folio]);

        foreach ($itemsToDelete as $item) {
            $manager->remove($item);
        }

        // $repositoryR = $manager->getRepository(RecordCCP::class);
        // $itemsToDeleteR = $repositoryR->findBy(['id' => $folio]);
        // foreach ($itemsToDeleteR as $items) {
        //     $manager->remove($items);
        // }

        // Flushear los cambios y limpiar el caché
        $manager->flush();
        $manager->clear();

        // Redirigir 
        return $this->redirectToRoute('index_ccp');
    }
}
