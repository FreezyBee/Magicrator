<?php

namespace FreezyBee\Magicrator\Forms;

use FreezyBee\Magicrator\IGenerator;
use Nette\Object;
use Nette\Utils\FileSystem;

/**
 * Class FormGenerator
 * @package FreezyBee\Magicrator\Forms\BaseFormGenerator
 */
class BaseFormGenerator extends Object implements IGenerator
{
    /**
     * @param string $dir
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     */
    public function generateToFile($dir, $componentNamespace, $entityNamespace = null, $facadeNamespace = null)
    {
        $dir = rtrim($dir, '/');

        FileSystem::write($dir . '/BaseForm.php', "<?php\n\n" . $this->generate($componentNamespace));
    }

    /**
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     * @return string
     */
    public function generate($componentNamespace, $entityNamespace = null, $facadeNamespace = null)
    {
        $namespace = substr($componentNamespace, 0, strrpos($componentNamespace, '\\'));

        return 'namespace ' . $namespace . ';
    
use FreezyBee\Forms\Services\FormService;
use FreezyBee\Forms\ValidatorException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Class BaseForm
 * @package ' . $namespace . '
 */
abstract class BaseForm extends Control
{
    /**
     * @var array|\Closure[]
     */
    public $onSuccess = [];

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var mixed
     */
    protected $id;
    
    /**
     * @var FormService
     */
    protected $formService;

    /**
     * BaseForm constructor.
     * @param FormService $formService
     */
    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * @return Form
     */
    abstract protected function createComponentForm();

    /**
     * @param Form $form
     * @param \stdClass $values
     */
    public function process(Form $form, \stdClass $values)
    {
        try {
            $this->formService->saveForm($this->entity, $form);

        } catch (ValidatorException $e) {
            $unknownError = $e->getUnclassifiableErrors();

            if (count($unknownError)) {
                if ($this->presenter) {
                    $this->presenter->flashMessage($unknownError, \'danger\');
                } else {
                    $form->addError($unknownError);
                }
            }
            return;
        }

        $this->onSuccess($form);
    }

    /**
     *
     */
    public function render()
    {
        $this[\'form\']->render();
    }
}' . "\n";
    }
}
