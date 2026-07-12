<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Database\Exception\QueryException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class UsersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['index'],
            'modelClass' => 'Users',
            'strictMode' => true,
        ]);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->authorize($this->Users);
        $query = $this->Users->find('search', search: $this->request->getQueryParams());
        $search = trim((string)$this->request->getQuery('q', ''));
        $users = $this->paginate($query);

        $this->set(compact('users', 'search'));
    }

    /**
     * View method
     *
     * @param string $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id)
    {
        $user = $this->Users->get($id, contain: []);
        $this->Authorization->authorize($user);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->authorize($this->Users);
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id)
    {
        $user = $this->Users->get($id, contain: []);
        $this->Authorization->authorize($user);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $this->Authorization->authorize($user);
        try {
            $deleted = $this->Users->delete($user);
        } catch (QueryException) {
            $deleted = false;
        }
        if (!$deleted) {
            $this->Flash->error(__('This user cannot be deleted because it has related operational records. Deactivate the account instead.'));
        } else {
            $this->Flash->success(__('The record has been deleted.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
