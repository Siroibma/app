<?php
namespace App\Controller\Api;

use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(['add', 'token']);
    }

    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $this->Users->save($user);
            $this->set('data', [
                'id' => $user->id,
                'token' => JWT::encode(
                    [
                        'sub' => $user->id,
                        'exp' =>  time() + 604800
                    ],
                Security::getSalt())
            ]);
        }
        $this->viewBuilder()->setOption('serialize', ['data']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function token()
    {
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException('Invalid username or password');
        }

        $this->set([
            'success' => true,
            'data' => [
                'id' => $user['id'],
                'token' => JWT::encode([
                    'sub' => $user['id'],
                    'exp' =>  time() + 604800
                ],
                Security::getSalt())
            ]
        ]);
        $this->viewBuilder()->setOption('serialize', ['data']);
        $this->RequestHandler->renderAs($this, 'json');
    }
}

?>