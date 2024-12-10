<?php

namespace App\Models\Specific;

use PKPass\PKPass;

class PKPassGenerator {

    private static $teamIdentifier = 'MM4KH4WBVA';
    private static $passTypeIdentifier = 'pass.kz.arenatickets';

    public static function generateTicket(OrderItem $orderItem, $output = false) {
        $order = $orderItem->order;
        if(!$order) return null;
        $timetable = $order->timetable;
        if(!$timetable) return null;
        $show = $timetable->show;
        if(!$show) return null;
        $venue = $timetable->venue;
        if(!$venue) return null;
        $pass = new PKPass(storage_path('certs/Certificates.p12'), 'arenatickets');
        $data = [
            'description' => 'Билет на событие от Arenatickets',
            'formatVersion' => 1,
            'organizationName' => 'Arenatickets',
            'passTypeIdentifier' => self::$passTypeIdentifier,
            'serialNumber' => str_pad($orderItem->id, 11, '0'),
            'teamIdentifier' => self::$teamIdentifier,
            'eventTicket'           => [
//                'headerFields'          => [
//                    [
//                        'key'               => 'event',
//                        'label'             => 'Событие',
//                        'value'             => $show->title
//                    ]
//                ],
                'primaryFields'         => [],
                'secondaryFields'       => [
                    [
                        'key'               => 'location',
                        'label'             => 'ДАТА / ПЛОЩАДКА',
                        'value'             => $timetable->dateString.' / '.$venue->title
                    ],
                ],
                'auxiliaryFields'       => [
                    [
                        'key'               => 'seat',
                        'label'             => 'МЕСТА',
                        'value'             => $orderItem->fullSeatName
                    ]
                ],
            ],
//            'webServiceURL' => 'https://api.arenatickets.kz/',
            'barcode' => [
                'format'            => 'PKBarcodeFormatQR',
                'message'           => $orderItem->barcode,
                'messageEncoding'   => 'iso-8859-1',
            ],
//            'labelColor'        => 'rgb(255,255,255)',
            'labelColor'        => 'rgb(155,155,155)',
//            'foregroundColor' => 'rgb(123,12,65)',
            'foregroundColor'   => 'rgb(0,0,0)',
//            'backgroundColor' => 'rgb(32,110,247)',
//            'backgroundColor' => 'rgb(230,230,230)',
//            'backgroundColor'   => 'rgb(0,0,0)',
            'backgroundColor'   => 'rgb(255,255,255)',
//            'backgroundColor' => 'rgb(75,176,254)',
            'logoText'          => $show->title,
            'relevantDate'      => date('Y-m-d\TH:i:sP', strtotime($timetable->date))
        ];

        $pass->setData($data);

        // Add files to the pass package
        $pass->addFile($show->getPKPassStripImage());
        $pass->addFile(public_path('images/pkpass/icon.png'));
        $pass->addFile(public_path('images/pkpass/icon@2x.png'));
        $pass->addFile(public_path('images/pkpass/logo.png'));
        $pass->addFile(public_path('images/pkpass/logo@2x.png'));

        // Create and output the pass
        return $pass->create($output);

    }
}
