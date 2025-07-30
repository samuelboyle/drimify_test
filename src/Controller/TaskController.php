<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task', methods: ['GET', 'POST'])]
    public function index(Request $request, TaskRepository $tasksRepository, EntityManagerInterface $em): Response
    {

        $tasks = $tasksRepository->getAllTasks();

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            if ($title) {
                foreach ($tasks as $Task) {
                    # Compare the posted title with existing task titles to prevent duplicate task names
                    if (strtolower($Task->getTitle()) == strtolower($title)){
                        $this->addFlash('error', 'Task with this title already exists.');
                        return $this->redirectToRoute('app_task');
                    }
                }

                $task = new Task();
                $task->setTitle($title);
                $task->setIsDone(false);
                $task->setCreatedAt(new \DateTime());
                $task->setUpdatedAt(new \DateTime());
                $em->persist($task);
                $em->flush();
                return $this->redirectToRoute('app_task');
            }
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Toggle task completion status
     */
    #[Route('/tasks/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggle(int $id, TaskRepository $tasksRepository, EntityManagerInterface $em): Response
    {
        $task = $tasksRepository->getTaskById($id);
        if ($task) {
            $task->setIsDone(!$task->isDone());
            $task->setUpdatedAt(new \DateTime());
            $em->flush();
        }
        return $this->redirectToRoute('app_task');
    }

    /**
     * Delete a task
     */
    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(int $id, TaskRepository $taskRepository, EntityManagerInterface $em): Response
    {
        $task = $taskRepository->find($id);
        if ($task) {
            $task->setDeletedAt(new \DateTime());
            $em->flush();
        }
        return $this->redirectToRoute('app_task');
    }
}
