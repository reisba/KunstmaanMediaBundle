<?php

namespace Kunstmaan\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Kunstmaan\MediaBundle\Entity\AbstractMediaMetadata;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use Kunstmaan\AdminBundle\Helper\ClassLookup;
use Kunstmaan\MediaBundle\Event\MediaEvent;
use Kunstmaan\MediaBundle\Event\Events;
use Kunstmaan\MediaBundle\Form\BulkUploadType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Form\VideoType;
use Kunstmaan\MediaBundle\Entity\Video;
use Kunstmaan\MediaBundle\Form\SlideType;
use Kunstmaan\MediaBundle\Entity\Slide;
use Kunstmaan\MediaBundle\Entity\Image;
use Kunstmaan\MediaBundle\Entity\File;
use Kunstmaan\MediaBundle\Form\MediaType;
use Kunstmaan\MediaBundle\Helper\MediaHelper;
use Kunstmaan\MediaBundle\Helper\BulkUploadHelper;
use Kunstmaan\MediaBundle\Entity\Folder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Kunstmaan\MediaBundle\Helper\MediaManager;

/**
 * MediaController
 */
class MediaController extends Controller
{

    /**
     * @param int $mediaId
     *
     * @Route("/{mediaId}", requirements={"mediaId" = "\d+"}, name="KunstmaanMediaBundle_media_show")
     *
     * @return Response
     */
    public function showAction($mediaId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        /* @var Media $media */
        $media = $em->getRepository('KunstmaanMediaBundle:Media')->getMedia($mediaId);
        $folder = $media->getFolder();

        /* @var MediaManager $mediaManager */
        $mediaManager = $this->get('kunstmaan_media.media_manager');
        $handler = $mediaManager->getHandler($media);
        $helper = $handler->getFormHelper($media);

        $form = $this->createForm($handler->getFormType(), $helper);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $media = $helper->getMedia();
                $em->getRepository('KunstmaanMediaBundle:Media')->save($media);

                return new RedirectResponse($this->generateUrl('KunstmaanMediaBundle_media_show', array('mediaId'  => $media->getId())));
            }
        }
        $showTemplate = $mediaManager->getHandler($media)->getShowTemplate($media);

        return $this->render($showTemplate, array(
                'mediamanager' => $this->get('kunstmaan_media.media_manager'),
                'form'      => $form->createView(),
                'media' => $media,
                'folder' => $folder));
    }

    /**
     * @param int $mediaId
     *
     * @Route("/delete/{mediaId}", requirements={"mediaId" = "\d+"}, name="KunstmaanMediaBundle_media_delete")
     *
     * @return RedirectResponse
     */
    public function deleteAction($mediaId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Media $media */
        $media = $em->getRepository('KunstmaanMediaBundle:Media')->getMedia($mediaId);
        $folder = $media->getFolder();

        $em->getRepository('KunstmaanMediaBundle:Media')->delete($media);

        return new RedirectResponse($this->generateUrl('KunstmaanMediaBundle_folder_show', array(
                'folderId'  => $folder->getId(),
                'slug' => $folder->getSlug())));
    }

    /**
     * @param int $folderId
     *
     * @Route("bulkupload/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_media_bulk_upload")
     * @Method({"GET", "POST"})
     * @Template("KunstmaanMediaBundle:File:bulkupload.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws \InvalidArgumentException when the gallery does not support bulk upload
     */
    public function bulkUploadAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);

        $request = $this->getRequest();
        $helper  = new BulkUploadHelper();

        $form = $this->createForm(new BulkUploadType('*/*'), $helper);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                foreach ($helper->getFiles() as $file) {
                    /* @var Media $media */
                    $media = $this->get('kunstmaan_media.media_manager')->getHandler($file)->createNew($file);
                    $media->setGallery($folder);
                    $em->getRepository('KunstmaanMediaBundle:Media')->save($media);
                }

                return new RedirectResponse($this->generateUrl('KunstmaanMediaBundle_folder_show', array(
                    'folderId'  => $folder->getId(),
                    'slug' => $folder->getSlug()
                )));
            }
        }

        $formView = $form->createView();
        $filesfield = $formView->children['files'];
        $filesfield->set('full_name', 'kunstmaan_mediabundle_bulkupload[files][]');

        return array(
            'form'      => $formView,
            'folder'   => $folder
        );

    }

    /**
     * @param int $folderId
     *
     * @Route("drop/{folderId}", requirements={"folderId" = "\d+"}, name="KunstmaanMediaBundle_media_drop_upload")
     * @Method({"GET", "POST"})
     *
     * @return array|RedirectResponse
     *
     * @throws \InvalidArgumentException when the gallery does not support bulk upload
     */
    public function dropAction($folderId)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);

        $request = $this->getRequest();

        $drop = null;
        if (array_key_exists('files', $_FILES) && $_FILES['files']['error'] == 0 ) {
            $pic = $_FILES['files'];
            $drop = $this->getRequest()->files->get('files');
        } else {
            $drop = $this->getRequest()->get('text');
        }
        $media = $this->get('kunstmaan_media.media_manager')->createNew($drop);
        if ($media) {
            $media->setFolder($folder);
            $em->getRepository('KunstmaanMediaBundle:Media')->save($media);

            return new Response(json_encode(array('status'=>'File was uploaded successfuly!')));
        }

        $this->getRequest()->getSession()->getFlashBag()->add('notice', 'Could not recognize what you dropped!');

        return new Response(json_encode(array('status'=>'Could not recognize anything!')));
    }

    /**
     * @param int    $folderId The folder id
     * @param string $type     The type
     *
     * @Route("create/{folderId}/{type}", requirements={"folderId" = "\d+", "type" = ".+"}, name="KunstmaanMediaBundle_media_create")
     * @Method({"GET", "POST"})
     * @Template("KunstmaanMediaBundle:File:create.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function createAction($folderId, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        /* @var Folder $folder */
        $folder = $em->getRepository('KunstmaanMediaBundle:Folder')->getFolder($folderId);

        $mediaManager = $this->get('kunstmaan_media.media_manager');
        $handler = $mediaManager->getHandlerForType($type);
        $helper  = $handler->createNewHelper();

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('media', $handler->getFormType());
        $formBuilder->setData(array('media' => $helper));
        $form = $formBuilder->getForm();

        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                if ($helper->getMedia() != null) {
                    $media = $helper->getMedia();
                    $media->setName($helper->getMedia()->getClientOriginalName());
                    $media->setContent($helper->getMedia());
                    $media->setGallery($folder);

                    $em->getRepository('KunstmaanMediaBundle:Media')->save($media);

                    return new RedirectResponse($this->generateUrl('KunstmaanMediaBundle_folder_show', array('folderId'  => $folder->getId(),
                        'slug' => $folder->getSlug()
                    )));
                }
            }
        }

        return array(
            'form'      => $form->createView(),
            'folder'   => $folder
        );
    }

}
