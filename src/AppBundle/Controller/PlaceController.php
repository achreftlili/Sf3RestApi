<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Place;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use AppBundle\Form\PlaceType;

class PlaceController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  section="Place services",
     *  resource = true,
     *  description = "Get list of Places",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Not Found"
     *   }
     * )
     * @Rest\Get("/places")
     */
    public function cgetAction()
    {
        $places = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Place')
                ->findAll();

        if ($places === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'No Places Found.');
        }

        return $this->responseFormatter($places);
    }
    /**
     *  @ApiDoc(
     *  section="Place services",
     *  resource = true,
     *  description = "Get a Place by Id",
     *  statusCodes = {
     *     200 = "Returned with successful",
     *     404 = "Article not found"
     *   }
     * )
     * @Rest\Get("/place/{id}")
     */
    public function getAction($id)
    {
        $place = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Place')
                ->find($id);
        if ($place === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'No Places Found.');
        }

        return $this->responseFormatter($place);
    }

   /**
    * @ApiDoc(
    *  section="Place services",
    *  resource = true,
    *  description = "Update a Place",
    *  input = {"class" = "AppBundle\Form\PlaceType",
    *            "name"=""
    * },
    *  name = "",
    *  statusCodes = {
    *     204 = "Returned when edited successfully",
    *     404 = "Place not found",
    *     422 = "Unprocessable Entity",
    *   }
    * )
    * @Rest\Put("/place/{id}")
    */
   public function putAction($id, Request $request)
   {
       $em = $this->getDoctrine()->getManager();
       $place = $em->getRepository('AppBundle:Place')->find($id);
       if ($place === null) {
           return new View('Place not found', Response::HTTP_NOT_FOUND);
       }
       $form = $this->createForm(PlaceType::class, $place);
       $form->submit($request->request->all());

       $validator = $this->get('validator');
       $errors = $validator->validate($place);
       
       if (count($errors) === 0) {
           $em->persist($place);
           $em->flush();

           return new View(null, Response::HTTP_NO_CONTENT);
       }
       throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Errot in Sended Data.');
   }

      /**
       * @ApiDoc(
       *  section="Place services",
       *  resource = true,
       *  description = "Create new Place",
       *  input = {"class" = "AppBundle\Form\PlaceType",
       *            "name"=""
       * },
       *  statusCodes = {
       *     201 = "Returned when successful",
       *     404 = "Place not found",
       *     422 = "Unprocessable Entity",
       *   }
       *)
       * @Rest\Post("/place/")
       */
      public function postAction(Request $request)
      {
          $place = new Place();
          $form = $this->createForm(PlaceType::class, $place);

          $form->submit($request->request->all());

          $validator = $this->get('validator');
          $errors = $validator->validate($place);
          if (count($errors) === 0) {
              $em = $this->getDoctrine()->getManager();
              $em->persist($place);
              $em->flush();

              return $this->redirectView(
                   $this->generateUrl(
                       'app_place_get',
                       array('id' => $place->getId())
                       ),
                   Response::HTTP_CREATED
                   );
          }
          throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Error in Sended Data.');
      }

    /**
     *  @ApiDoc(
     *  section="Place services",
     *  resource = true,
     *  description = "Delete a Place",
     *  statusCodes = {
     *     204 = "Returned when successful",
     *     404 = "Place not found"
     *   }
     * )
     * @Rest\Delete("/place/{id}")
     */
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository('AppBundle:Place')->find($id);
        if ($place === null) {
            return new View('Place not found', Response::HTTP_NOT_FOUND);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($place);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    public function responseFormatter($data = null, $code = 200, $message = null, $status = 'success')
    {
        $content = array();
        $content['message'] = $message;
        $content['status'] = $status;
        $content['code'] = $code;
        $content['data'] = $data;

        return $content;
    }
}
