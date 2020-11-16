<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AjaxController extends AbstractController
{
    /**
     * @Route("/more-trick", name="more_trick")
     */
    public function moreTrick(TrickRepository $trickRepository)
    {
        $offset = $_POST['offset'];
        $limit = 10;

        $tricks = $trickRepository->findByRangeOf($offset, $limit);
        
        return $this->render('ajax/trick.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @Route("/more-comment", name="more_comment")
     */
    public function moreComment(CommentRepository $commentRepository)
    {  
        $offset = $_POST['offset'];
        $id = $_POST['trick'];
        $limit = 4;

        $comments = $commentRepository->findByRangeOf($offset, $limit, $id);
        
        return $this->render('ajax/comment.html.twig', [
            'comments' => $comments,
        ]);
    }
}
