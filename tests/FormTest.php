<?php

namespace Pop\Form\Test;

use Pop\Form\Form;
use Pop\Form\Element;

class FormTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $_SERVER['REQUEST_URI'] = '/process';
        $form = Form::createFromConfig([
            'username' => [
                'type'     => 'text',
                'label'    => 'Username:',
                'required' => true
            ],
            'file' => [
                'type'  => 'file',
                'label' => 'File:'
            ],
            'submit' => [
                'type'  => 'submit',
                'value' => 'SUBMIT'
            ]
        ]);
        $this->assertInstanceOf('Pop\Form\Form', $form);
        $this->assertEquals(3, count($form->getFields()));
    }

}
