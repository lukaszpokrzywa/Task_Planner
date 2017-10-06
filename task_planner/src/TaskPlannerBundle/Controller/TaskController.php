<?php

namespace TaskPlannerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TaskPlannerBundle\Form\CategoryType;
use TaskPlannerBundle\Entity\Category;
use Symfony\Component\Form\Form;
use TaskPlannerBundle\Entity\Task;
use TaskPlannerBundle\Form\TaskType;
use TaskPlannerBundle\Entity\Comment;
use TaskPlannerBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\MonologBundle\SwiftMailer;

/**
 * Class TaskController
 * @Route("/task")
 */

class TaskController extends Controller
{
    /**
     * @Route("/new", name = "createTask")
     * @Method("GET")
     */
    public function newTaskFormAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task, array(
            'action' => $this->generateUrl('newTask'),
            'user' => $this->getUser()
        ));
        $form->handleRequest($request);

        return $this->render('TaskPlannerBundle:Task:new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/new", name = "newTask")
     * @Method("POST")
     */
    public function newTaskAction(Request $request)
    {
        $task = new Task();
        $user = $this->getUser();

        $form = $this->createForm(TaskType::class, $task, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $task = $form->getData();
            $task->setUser($user);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
            return $this->redirectToRoute('showTask', array(
                'id' => $task->getId()
            ));
        }

        return new Response("Incorrect data. Try create Task again.");


    }

    /**
     * @Route("/{id}/edit" , name = "editFormTask")
     * @Method("GET")
     */
    public function editFormTaskAction(Request $request, $id)
    {

        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $task = $repo->find($id);

        if($task) {
            $this->denyAccessUnlessGranted('edit', $task);

            $form = $this->createForm(TaskType::class, $task, array(
                'action' => $this->generateUrl('editTask', array('id' => $id)),
                'user' => $this->getUser()
            ));

            $form->handleRequest($request);

            return $this->render('TaskPlannerBundle:Task:edit.html.twig', array(
                'form' => $form->createView(),
                'task' => $task
            ));

        }

        return new Response("Task not found");
    }



    /**
     * @Route("/{id}/edit", name = "editTask")
     * @Method("POST")
     */
    public function editTaskAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $task = $repo->find($id);

        $form = $this->createForm(TaskType::class, $task, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $task = $form->getData();
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('editTask', array(
                'id'=>$task->getId()
            ));
        }

        return new Response("Incorrect data. Try edit task again.");

    }


    /**
     * @Route("/{id}/delete", name = "deleteTask")
     */
    public function deleteTaskAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $task = $repo->find($id);

        if($task) {
            $this->denyAccessUnlessGranted('edit', $task);


            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();
        }

            return $this->redirectToRoute('showAllCategories');
    }

    /**
     * @Route("/", name = "showAllTasks")
     */
    public function showAllTasksAction()
    {
        $user = $this->getUser()->getId();
        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $tasks = $repo->findBy(['user' => $user]);

        return $this->render('TaskPlannerBundle:Task:showAll.html.twig', array(
            'tasks' => $tasks
        ));
    }

    /**
     * @Route("/{id}", name="showTask", requirements = {"id" = "\d+"})
     */
    public function showTaskAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $task = $repo->find($id);

        $comments = $task->getComments();

        if($task) {
            $this->denyAccessUnlessGranted('view', $task);

            $comment = new Comment();
            $form = $this->createForm(CommentType::class, $comment, array(
                'action' => $this->generateUrl('newComment', array(
                'taskId' => $id
                ))
            ));

            return $this->render('TaskPlannerBundle:Task:show.html.twig', array(
                'task' => $task,
                'form' => $form->createView(),
                'comments' => $comments
            ));

        }

        return new Response("Task not found");
    }


    /**
     * @Route("/{id}/change", name = "changeStatus")
     * @Method("GET")
     */
    public function changeStatusAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('TaskPlannerBundle:Task');
        $task = $repo->find($id);

        if ($task && $task->getIsDone() == false) {
            $task->setIsDone(true);
        }elseif($task && $task->getIsDone() == true) {
            $task->setIsDone(false);
        }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('mainNotDone');
    }



    }
