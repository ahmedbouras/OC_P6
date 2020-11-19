<?php

namespace App\Controller;

use App\Entity\Video;

class VideoHandler
{
    public function makeLinkToEmbed(string $link): string
    {
        if (preg_match('#youtube#', $link)) {
            $splitedLink = preg_split('#&#', $link);
            $embeddingLink = preg_replace('#watch\?v=#', 'embed/', $splitedLink[0]);
            return $embeddingLink;
        } elseif (preg_match('#dailymotion#', $link)) {
            $embeddingLink = preg_replace('#video#', 'embed/video', $link);
            return $embeddingLink;
        }
    }

    public function setEntity($trick, $embeddedLink)
    {
        $video = new Video();
        $video->setTrick($trick)->setName($embeddedLink);
        return $video;
    }
}