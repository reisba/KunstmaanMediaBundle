<?php

namespace Kunstmaan\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Kunstmaan\MediaBundle\Form\FolderType;
use Kunstmaan\MediaBundle\Entity\ImageGallery;
use Kunstmaan\MediaBundle\Form\SubFolderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * imagegallery controller.
 *
 * @author Kristof Van Cauwenbergh
 */
class ImageGalleryController extends Controller
{
    /**
     * @Route("/ckeditor", name="KunstmaanMediaBundle_imagegallery_ckeditor")
     * @Template()
     */
    public function ckeditorAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFoldersByType();

        return array(
            'galleries'     => $galleries
        );
    }

    /**
     * @Route("/filechooser", name="KunstmaanMediaBundle_imagegallery_filechooser")
     * @Template()
     */
    public function filechooserAction() {
    	$em = $this->getDoctrine()->getEntityManager();
    	$galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFoldersByType();

    	return array(
    			'galleries'     => $galleries
    	);
    }

    /**
     * @Route("/imagepagepart", name="KunstmaanMediaBundle_imagegallery_imagepagepart")
     * @Template()
     */
    public function imagepagepartAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFoldersByType();

        return array(
            'galleries'     => $galleries
        );
    }

    /**
     * @Route("/filechooser/{id}/{slug}", requirements={"id" = "\d+"}, name="KunstmaanMediaBundle_filechooser_show")
     * @Template()
     */
    function filechoosershowfolderAction($id){
    	$em = $this->getDoctrine()->getEntityManager();
    	$gallery = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($id, $em);
    	$galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFoldersByType();

    	return array(
    			'gallery'       => $gallery,
    			'galleries'     => $galleries
    	);
    }

    /**
     * @Route("/ckeditor/{id}/{slug}", requirements={"id" = "\d+"}, name="KunstmaanMediaBundle_ckeditor_show")
     * @Template()
     */
    function showfolderAction($id){
    	$em = $this->getDoctrine()->getEntityManager();
    	$gallery = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($id, $em);
    	$galleries = $em->getRepository('KunstmaanMediaBundle:Folder')->getAllFoldersByType();

    	return array(
            'gallery'       => $gallery,
            'galleries'     => $galleries
    	);
    }

}