<?php
namespace ConstructionsIncongrues\EmailController\Event;

use AramisAuto\EmailController\Event\MessageEvent;
use AramisAuto\EmailController\Message;

class FileHostingServiceEvent extends MessageEvent
{
    public function __construct(Message $message, $data = array())
    {
        parent::__construct($message, $data);
        foreach (array('service', 'urls') as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Missing data key : %s', $key));
            }
        }
    }

    public function getService()
    {
        return $this->getData()['service'];
    }

    public function getUrls()
    {
        return $this->getData()['urls'];
    }
}
