<?php

namespace Dcat\Admin\Extension\Setting\Actions\Grid;

use Dcat\Admin\Admin;
use Dcat\Admin\Extension\Setting\Forms\ChooseFormType;
use Dcat\Admin\Grid\Tools\AbstractTool;

class AddSetting extends AbstractTool
{
    public function render()
    {
        $this->modal();

        $title = $this->title;
        return <<<HTML
<span class="grid-expand" data-toggle="modal" data-target="#choose_form_type">
   <a href="javascript:void(0)" class="btn btn-primary">{$title}</a>
</span>
HTML;
    }

    private function modal()
    {
        $form = new ChooseFormType();
        $title = '选择表单类型';
        Admin::html(
            <<<HTML
<div class="modal fade" id="choose_form_type">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$title}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {$form->render()}
            </div>
        </div>
    </div>
</div>
HTML
        );
    }
}
