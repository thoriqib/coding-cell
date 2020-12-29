<?php

namespace App\Http\Controllers;

use \App\Gateway\EventLogGateway;
use \App\Gateway\GadgetGateway;
use \App\Gateway\UserGateway;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;
use \Illuminate\Log\Logger;
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;

class Webhook extends Controller
{
    /**
     * @var LINEBot
     */
    private $bot;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var EventLogGateway
     */
    private $logGateway;
    /**
     * @var UserGateway
     */
    private $userGateway;
    /**
     * @var GadgetGateway
     */
    private $gadgetGateway;
    /**
     * @var array
     */
    private $user;


    public function __construct(
        Request $request,
        Response $response,
        Logger $logger,
        EventLogGateway $logGateway,
        UserGateway $userGateway,
        GadgetGateway $gadgetGateway
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        $this->logGateway = $logGateway;
        $this->userGateway = $userGateway;
        $this->gadgetGateway = $gadgetGateway;

        // create bot object
        $httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    public function __invoke()
    {
        // get request
        $body = $this->request->all();

        // debuging data
        $this->logger->debug('Body', $body);

        // save log
        $signature = $this->request->server('HTTP_X_LINE_SIGNATURE') ?: '-';
        $this->logGateway->saveLog($signature, json_encode($body, true));

        return $this->handleEvents();
    }

    private function handleEvents()
    {
        $data = $this->request->all();

        if (is_array($data['events'])) {
            foreach ($data['events'] as $event) {
                // skip group and room event
                if (!isset($event['source']['userId'])) continue;

                // get user data from database
                $this->user = $this->userGateway->getUser($event['source']['userId']);

                // if user not registered
                if (!$this->user) $this->followCallback($event);
                else {
                    // respond event
                    // if ($event['type'] == 'message') {
                    //     if (method_exists($this, $event['message']['type'] . 'Message')) {
                    //         $this->{$event['message']['type'] . 'Message'}($event);
                    //     }
                    // } else {
                    //     if (method_exists($this, $event['type'] . 'Callback')) {
                    //         $this->{$event['type'] . 'Callback'}($event);
                    //     }
                    // }
                    $this->searchGadget($event);
                }
            }
        }


        $this->response->setContent("No events found!");
        $this->response->setStatusCode(200);
        return $this->response;
    }

    private function followCallback($event)
    {
        $res = $this->bot->getProfile($event['source']['userId']);
        if ($res->isSucceeded()) {
            $profile = $res->getJSONDecodedBody();

            // create welcome message
            $message  = "Halo, " . $profile['displayName'] . "!\n";
            $message .= "Silakan ketikkan kata kunci untuk mendapatkan informasi";
            $textMessageBuilder = new TextMessageBuilder($message);

            // create sticker message
            $stickerMessageBuilder = new StickerMessageBuilder(1, 3);

            // merge all message
            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
            $multiMessageBuilder->add($stickerMessageBuilder);

            // send reply message
            $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);

            // save user data
            $this->userGateway->saveUser(
                $profile['userId'],
                $profile['displayName']
            );
        }
    }

    private function searchGadget($event)
    {
        $userMessage = $event['message']['text'];

        $gadget = $this->gadgetGateway->getGadget($userMessage);

        if ($gadget != null) {
            if (count($gadget) > 1) {
                $carouselData = array();
                foreach ($gadget as $g) {
                    $text = $g['harga'] . "\n" . $g['deskripsi'];
                    $searchGSMArena = str_replace(" ", "+", $g['nama']);
                    $template = new CarouselColumnTemplateBuilder(
                        $g['nama'],
                        $text,
                        $g['image'],
                        [new UriTemplateActionBuilder('Spesifikasi Lengkap', "https://www.gsmarena.com/res.php3?sSearch=$searchGSMArena")]
                    );
                    array_push($carouselData, $template);
                }
                $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselData);
                $templateMessage = new TemplateMessageBuilder('carousel template', $carouselTemplateBuilder);
                $this->bot->replyMessage($event['replyToken'], $templateMessage);
            } else if (count($gadget == 1)) {
                $gadgetData = $gadget[0];
                $text = $gadgetData['harga'] . "\n" . $gadgetData['deskripsi'];
                $searchGSMArena = str_replace(" ", "+", $gadgetData['nama']);
                $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    $gadgetData['nama'],
                    $text,
                    $gadgetData['image'],
                    [new UriTemplateActionBuilder('Spesifikasi Lengkap', "https://www.gsmarena.com/res.php3?sSearch=$searchGSMArena")]
                );
                $templateMessage = new TemplateMessageBuilder('button template', $buttonTemplateBuilder);
                $this->bot->replyMessage($event['replyToken'], $templateMessage);
            }
        } else {
            $message = "Gadget yang kamu cari tidak ada di database kami";
            $stickerMessageBuilder = new StickerMessageBuilder(2, 153);
            $textMessageBuilder = new TextMessageBuilder($message);

            $multiMessageBuilder = new MultiMessageBuilder();
            $multiMessageBuilder->add($textMessageBuilder);
            $multiMessageBuilder->add($stickerMessageBuilder);

            $this->bot->replyMessage($event['replyToken'], $multiMessageBuilder);
        }
    }
}
