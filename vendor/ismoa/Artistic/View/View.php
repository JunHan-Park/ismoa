<?php

class View
{
    private $path = null;
    private $view = null;
    private $isview = false;
    private $separator = DIRECTORY_SEPARATOR;

    use \Artistic\Traits\Singleton;

    private function __construct()
    {
        $this->path = realpath(__DIR__ . '/../../../../app/Http/Views');
    }

    private function setView()
    {
        $this->view = $this->path .'/'. $this->view.'.html';
    }

    private function isView()
    {
        return (file_exists($this->view) && is_file($this->view)) ? true : false;
    }

    public function renderView($url, array $data)
    {
        try {
            $this->view = str_replace('.', $this->separator, $url);
            $this->setView();

            if (true !== $this->isView()) throw new ArtisticException('view not found', 404);

            ini_set('include_path', realpath(__DIR__.'/../../../../app/Http/Views/'));
            extract($data);

            register_shutdown_function(function(){
                if (isset($_SESSION['msg'])) unset($_SESSION['msg']);
                if (isset($_SESSION['artistic']['input'])) unset($_SESSION['artistic']['input']);
            });
            exit(require($this->view));
        } catch(ArtisticException $e) {
            $e->getException($e);
        }
    }
}//end class