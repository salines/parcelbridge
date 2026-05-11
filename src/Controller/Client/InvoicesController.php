<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;

/**
 * Invoices Controller
 *
 * @property \App\Model\Table\InvoicesTable $Invoices
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class InvoicesController extends AppController
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
            'modelClass' => 'Invoices',
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
        $this->Authorization->authorize($this->Invoices);
        $query = $this->Authorization->applyScope(
            $this->Invoices->find('search', search: $this->request->getQueryParams())
                ->contain(['Packages', 'UploadedByUsers', 'ReviewedByUsers']),
        );
        $search = trim((string)$this->request->getQuery('q', ''));
        $invoices = $this->paginate($query);

        $this->set(compact('invoices', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function view(string $id)
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages', 'UploadedByUsers', 'ReviewedByUsers']);
        $this->Authorization->authorize($invoice);
        $this->set(compact('invoice'));
    }

    /**
     * Download an invoice file owned by the current client.
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null
     */
    public function downloadFile(string $id)
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice, 'view');

        $path = RESOURCES . str_replace('/', DS, $invoice->file_path);
        if (!is_file($path)) {
            $this->Flash->error(__('The invoice file could not be found.'));

            return $this->redirect(['action' => 'view', $invoice->id]);
        }

        return $this->response->withFile($path, [
            'download' => true,
            'name' => $invoice->original_filename,
        ]);
    }
}
