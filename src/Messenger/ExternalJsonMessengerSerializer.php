<?php

namespace App\Messenger;

use App\Message\Command\LogEmoji;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class ExternalJsonMessengerSerializer implements SerializerInterface
{

    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        $headers = $encodedEnvelope['headers'];

        $data = json_decode($body, true);

        if (null === $data) {
            throw new MessageDecodingFailedException('Invalid JSON'); // the queue is removed
        }

        if (!isset($data['emoji'])) {
            // throw new \Exception('Missing the emoji key!'); the queue remains and it fails again and again
            throw new MessageDecodingFailedException('Missing the emoji key');
        }

        $message = new LogEmoji($data['emoji']);

        // in cse of redelivery, unserialize any stamps
        $stamps =[];
        if (isset($headers['stamps'])) {
            $stamps = unserialize($headers['stamps']);
        }

        $envelope =  new Envelope($message, $stamps);

        // needed only if i need this to be sent through the non-default bus
        $envelope = $envelope->with(new BusNameStamp('command.bus'));

        return $envelope;
    }

    public function encode(Envelope $envelope): array
    {
        // throw new \Exception('Transport & serializer not meant for'); in case of no redelivery

        // this is called if a message is redelivered for "retry"
        $message = $envelope->getMessage();

        // expand this logic later if I handle more than just one message class
        if ($message instanceof LogEmoji) {
            // recreate what the data originally looked like
            $data = ['emoji' => $message->getEmojiIndex()];
        } else {
            throw new \Exception('Unsupported message class');
        }

        $allStamps = [];
        foreach ($envelope->all() as $stamps) {
            $allStamps = array_merge($allStamps, $stamps);
        }

        return [
            'body' => json_encode($data),
            'headers' => [
                // store stamps as a header - to be read in decode()
                'stamps' => serialize($allStamps),
            ],
        ];
    }
}
