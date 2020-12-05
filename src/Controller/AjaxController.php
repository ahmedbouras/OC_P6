<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AjaxController extends AbstractController
{
    private const TRICK_LIMIT = 10;
    private const COMMENT_LIMIT = 4;
    
    /**
     * @Route("/more-trick", name="more_trick")
     */
    public function moreTrick(TrickRepository $trickRepository)
    {
        $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $tricks = $trickRepository->findByRangeOf($offset, self::TRICK_LIMIT);
        
        return $this->render('ajax/trick.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * @Route("/more-comment", name="more_comment")
     */
    public function moreComment(CommentRepository $commentRepository)
    {  
        $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $id = isset($_POST['trick']) ? $_POST['trick'] : null;

        if(!$id) {
            $this->addFlash('danger', "Une erreur s'est produite lors du chargement des commentaires.");
            $this->redirectToRoute('home');
        }

        $comments = $commentRepository->findByRangeOf($offset, self::COMMENT_LIMIT, $id);
        
        return $this->render('ajax/comment.html.twig', [
            'comments' => $comments,
        ]);
    }
}
