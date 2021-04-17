<?php


namespace Pars\Core\Session;


use Mezzio\Session\SessionInterface;
use Pars\Mvc\View\State\ViewState;
use Pars\Mvc\View\State\ViewStatePersistenceInterface;

class SessionViewStatePersistence implements ViewStatePersistenceInterface
{
    /**
     * @var SessionInterface
     */
    protected SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /***
     * @param string $id
     * @param ViewState $state
     * @return mixed|void
     */
    public function save(string $id, ViewState $state)
    {
        $this->session->set($id, $state->toArray(true));
    }

    /**
     * @param string $id
     * @return ViewState
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    public function load(string $id): ViewState
    {
        $data = $this->session->get($id);
        $state = new ViewState($id);
        if (is_array($data)) {
            $state->fromArray($data);
        }
        return $state;
    }
}
