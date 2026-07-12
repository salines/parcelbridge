<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Database\Exception\QueryException;

/**
 * Clients Controller
 *
 * @property \App\Model\Table\ClientsTable $Clients
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class ClientsController extends AppController
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
            'modelClass' => 'Clients',
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
        $this->Authorization->authorize($this->Clients);
        $query = $this->Clients->find('search', search: $this->request->getQueryParams())
            ->contain(['Users'])
            ->select($this->Clients)
            ->select([
                'package_count' => $this->Clients->find()->func()->count('Packages.id'),
            ])
            ->leftJoinWith('Packages')
            ->groupBy(['Clients.id']);
        $search = trim((string)$this->request->getQuery('q', ''));
        $clients = $this->paginate($query);

        $this->set(compact('clients', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Client id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id)
    {
        $client = $this->Clients->get($id, contain: ['Users', 'Packages', 'ShipRequests']);
        $this->Authorization->authorize($client);
        $this->set(compact('client'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->authorize($this->Clients);
        $client = $this->Clients->newEmptyEntity();
        if ($this->request->is('post')) {
            $client = $this->Clients->patchEntity($client, $this->request->getData());
            if ($this->Clients->save($client)) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $users = $this->Clients->Users->find('list', limit: 200)->all();
        $this->set(compact('client', 'users'));
    }

    /**
     * Edit method
     *
     * @param string $id Client id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id)
    {
        $client = $this->Clients->get($id, contain: []);
        $this->Authorization->authorize($client);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $client = $this->Clients->patchEntity($client, $this->request->getData());
            if ($this->Clients->save($client)) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $users = $this->Clients->Users->find('list', limit: 200)->all();
        $this->set(compact('client', 'users'));
    }

    /**
     * Delete method
     *
     * @param string $id Client id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $client = $this->Clients->get($id);
        $this->Authorization->authorize($client);
        try {
            $deleted = $this->Clients->delete($client);
        } catch (QueryException) {
            $deleted = false;
        }
        if (!$deleted) {
            $this->Flash->error(__('This client cannot be deleted because it has related operational records.'));
        } else {
            $this->Flash->success(__('The record has been deleted.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
