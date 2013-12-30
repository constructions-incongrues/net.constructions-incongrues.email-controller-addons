<?php
namespace ConstructionsIncongrues\EmailController\MessageStrategy;

use AramisAuto\EmailController\Event\ErrorEvent;
use AramisAuto\EmailController\Event\MessageEvent;
use AramisAuto\EmailController\MessageStrategy\AbstractMessageStrategy;
use Goutte\Client;

class SendspaceMessageStrategy extends AbstractMessageStrategy
{
    public function execute()
    {
        try {
            // Find link to download page
            $matches = array();
            $matched = preg_match_all('|(http://www.sendspace.com/file/.+)|', $this->getMessage()->text, $matches);
            if (!$matched) {
                throw new \RuntimeException(
                    sprintf(
                        'Could not find link to download page in message body - %s',
                        json_encode(array('body' => $this->getMessage()->text))
                    )
                );
            }

            // Get file download url from download page
            $client = new Client();
            $urls = array();
            foreach ($matches[0] as $url) {
                $crawler = $client->request('GET', $url);
                $attributes = $crawler->filter('a#download_button')->extract(array('_text', 'href'));
                $urls[] = $attributes[0][1];
            }
            if (count($urls) > 0) {
                $event = new MessageEvent(
                    $this->getMessage(),
                    array(
                        'service'          => 'sendspace.com',
                        'urlsDownloadPage' => $matches[0],
                        'urlsDownloadFile' => $urls
                    )
                );
                $this->getEventDispatcher()->dispatch(AbstractMessageStrategy::EVENT_SUCCESS, $event);
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Could find URL to file on download page - %s',
                        json_encode(array('url' => $matches[0]))
                    )
                );
            }
        } catch (\Exception $e) {
            $event = new ErrorEvent($e->getMessage(), $e, array('message' => $this->getMessage()));
            $this->getEventDispatcher()->dispatch(AbstractMessageStrategy::EVENT_ERROR, $event);
        }
    }
}
