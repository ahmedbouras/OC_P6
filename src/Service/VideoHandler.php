<?php

namespace App\Service;

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
}