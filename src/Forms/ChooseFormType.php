<?php

namespace Dcat\Admin\Extension\Setting\Forms;

use Dcat\Admin\Form as MainForm;
use Dcat\Admin\Widgets\Form;
use Symfony\Component\HttpFoundation\Response;

class ChooseFormType extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return Response
     */
    public function handle(array $input)
    {
        return $this->redirect(route('setting.create', $input));
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $formTypeArray = array_keys((new \ReflectionClass(MainForm::class))->getStaticProperties()['availableFields']);
        $selectOptions = array_combine($formTypeArray, $formTypeArray);
        // unset no need form type.
        unset(
            $selectOptions['id'],
            $selectOptions['hidden'],
            $selectOptions['html'],
            $selectOptions['divider'],
            $selectOptions['display'],
            $selectOptions['captcha'],
            $selectOptions['embeds'],
            $selectOptions['markdown'],
            $selectOptions['hasMany'],
            $selectOptions['table'],
            $selectOptions['button']
        );

        $this->select('form_type', '表单类型')
            ->options($selectOptions)
            // radio & checkbox & slider & listbox
            ->when(['radio', 'checkbox', 'slider', 'listbox'], function (Form $form) {
                $form->textarea('text_area_1', '配置参数');
            })
            // select & multipleSelect & tags
            ->when(['select', 'multipleSelect', 'tags'], function (Form $form) {
                $form->radio('options_type', '数据类型')
                    ->when(0, function (Form $form) {
                        $form->text('api_url', '接口地址');
                    })
                    ->when(1, function (Form $form) {
                        $form->textarea('text_area_2', '配置参数');
                    })
                    ->options(['接口地址', '配置参数']);
            })
            // time & date & datetime
            ->when(['time', 'date', 'datetime'], function (Form $form) {
                $form->textarea('text_area_3', '时间日期格式');
            })
            // selectResource
            ->when('selectResource', function (Form $form) {
                $tips = '请输入回调函数，格式：类名称@方法名称, 举例：\\app\\Http\\Callback\\Example@test，系统会为test方法自动注入$item变量。';
                $form->radio('is_multiple', '是否多选')->options(['是', '否']);
                $form->text('api_url_for_select_resource', '接口地址');
                $form->textarea('callback', '回调')->help($tips);
            })
            ->required();

        $this->disableAjaxSubmit();
    }
}
