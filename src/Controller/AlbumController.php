<?php
// in module/Album/src/Controller/AlbumController.php:
namespace Album\Controller;

use Album\Model\AlbumTable;
use Album\Model\GenreTable;
use Laminas\Form\FormElementManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Album\Form\AlbumForm;
use Album\Model\Album;

class AlbumController extends AbstractActionController
{
  // Add this property:
   private $albumTable;
   private $genreTable;
   private FormElementManager $formElementManager;

   // Add this constructor:
   public function __construct(AlbumTable $albumTable, FormElementManager $formElementManager)
   {
       $this->albumTable = $albumTable;
       $this->formElementManager = $formElementManager;
   }

   public function indexAction()
   {
       // Grab the paginator from the AlbumTable:
       $paginator = $this->albumTable->fetchAll(true);

       // Set the current page to what has been passed in query string,
       // or to 1 if none is set, or the page is invalid:
       $page = (int) $this->params()->fromQuery('page', 1);
       $page = ($page < 1) ? 1 : $page;
       $paginator->setCurrentPageNumber($page);

       // Set the number of items per page to 10:
       $paginator->setItemCountPerPage(10);

       return new ViewModel(['paginator' => $paginator]);
   }

    public function addAction()
    {
        $form = $this->formElementManager->get(AlbumForm::class);

        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();

        if (! $request->isPost()) {
            return ['form' => $form];
        }

        $album = new Album();
        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $album->exchangeArray($form->getData());
        $this->saveAlbum($album);
        return $this->redirect()->toRoute('album');
    }

    public function editAction()
    {
      $id = (int) $this->params()->fromRoute('id', 0);

       if (0 === $id) {
           return $this->redirect()->toRoute('album', ['action' => 'add']);
       }

       // Retrieve the album with the specified id. Doing so raises
       // an exception if the album is not found, which should result
       // in redirecting to the landing page.
       try {
           $album = $this->albumTable->getAlbum($id);
       } catch (\Exception $e) {
           return $this->redirect()->toRoute('album', ['action' => 'index']);
       }

       $form = $this->formElementManager->get(AlbumForm::class);
       $form->bind($album);
       $form->get('submit')->setAttribute('value', 'Edit');

       $request = $this->getRequest();
       $viewData = ['id' => $id, 'form' => $form];

       if (! $request->isPost()) {
           return $viewData;
       }

       $form->setInputFilter($album->getInputFilter());
       $form->setData($request->getPost());

       if (! $form->isValid()) {
           return $viewData;
       }

       try {
           $this->albumTable->saveAlbum($album);
       } catch (\Exception $e) {
       }

       // Redirect to album list
       return $this->redirect()->toRoute('album', ['action' => 'index']);
     }


    public function deleteAction()
    {
      $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->albumTable->deleteAlbum($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return [
            'id'    => $id,
            'album' => $this->albumTable->getAlbum($id),
        ];
    }
}
