<?php

namespace Dcat\Admin\Extension\Setting\Http\Controllers;

use Dcat\Admin\Controllers\AdminController;
use Dcat\Admin\Extension\Setting\Actions\Grid\AddSetting;
use Dcat\Admin\Extension\Setting\Repositories\Setting;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Illuminate\Support\Str;

class SettingController extends AdminController
{

    /**
     * 展示配置信息
     *
     * @param  Content  $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title('系统配置')
            ->description('自定义系统配置')
            ->body($this->grid());
    }

    /**
     * 配置列表
     *
     * @return Grid
     */
    public function grid()
    {
        return Grid::make(new Setting(), function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableViewButton();

            $grid->id->sortable();
            $grid->form_type('表单类型');
            $grid->name;
            $grid->key('Key')->copyable();
            $grid->value('Value')
                // image multipleImage
                ->if(function ($value) {
                    return in_array($this->form_type, ['image', 'multipleImage']);
                })
                ->image('', 60, 60)
                // KeyValue
                ->if(function ($value) {
                    return $this->form_type == 'keyValue';
                })
                ->display(function ($value) {
                    $value = json_decode($value, true);
                    return collect($value)->map(function ($key, $val) {
                        return $key.'=>'.$val;
                    })->values()->implode('<br/>');
                })
                // file multipleFile
                ->if(function ($value) {
                    return in_array($this->form_type, ['file', 'multipleFile']);
                })
                ->downloadable()
                // dateRange timeRange range datetimeRange
                ->if(function ($value) {
                    return in_array($this->form_type, ['dateRange', 'timeRange', 'range', 'datetimeRange']);
                })
                ->display(function ($value) {
                    return $value[0].'至'.$value[1];
                })
                // select radio
                ->if(function ($value) {
                    return in_array($this->form_type, ['select', 'radio']);
                })
                ->display(function ($value) {
                    $options = json_decode($this->options, true);
                    if ($options) {
                        return $options[$value];
                    }
                })
                // multipleSelect checkbox listbox
                ->if(function ($value) {
                    return in_array($this->form_type, ['multipleSelect', 'checkbox', 'listbox']);
                })
                ->display(function ($value) {
                    $options = json_decode($this->options, true);
                    if ($options) {
                        return collect($options)->filter(function ($val, $key) use ($value) {
                            return in_array($key, $value);
                        })->implode('<br/>');
                    }
                })
                // list
                ->if(function ($value) {
                    return $this->form_type == 'list';
                })
                ->display(function ($value) {
                    $value = json_decode($value, true);
                    return implode('<br/>', $value);
                });
            $grid->created_at;
            $grid->updated_at;

            $grid->tools(new AddSetting('添加配置'));

            $grid->filter(function ($filter) {
                $filter->like('form_type', '表单类型');
                $filter->like('key', 'Key');
                $filter->like('name');
            });
        });
    }

    /**
     * 添加配置表单
     *
     * @return Form
     */
    public function form()
    {
        return Form::make(new Setting(), function (Form $form) {
            $form->disableViewCheck();
            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $formType = request()->input('form_type');

            if ($form->isCreating()) {
                $form->text('name', '配置名称')->required();
                $form->text('key', 'Key')->required();
                $form->hidden('form_type')->value($formType);

                switch ($formType) {
                    case 'map':
                    case 'timeRange':
                    case 'dateRange':
                    case 'datetimeRange':
                    case 'range':
                        $form->hidden('value');
                        $form->$formType('column_1', 'column_2', 'Value');
                        break;
                    case 'time':
                    case 'date':
                    case 'datetime':
                        $format = request()->input('text_area_3');
                        $form->hidden('options')->value($format);
                        $form->$formType('value', 'Value')->format($format);
                        break;
                    case 'checkbox':
                    case 'radio':
                    case 'slider':
                    case 'listbox':
                        $params = json_decode(request()->input('text_area_1'), true);
                        if (!$params) {
                            $params = [];
                        }
                        $form->hidden('options')->value(request()->input('text_area_1'));
                        $form->$formType('value', 'Value')->options($params);
                        break;
                    case 'select':
                    case 'multipleSelect':
                    case 'tags':
                        $params = request()->input('options_type') == 1
                            ? json_decode(request()->input('text_area_2'), true)
                            : request()->input('api_url');
                        !$params && $params = [];

                        $options = request()->input('options_type') == 1
                            ? request()->input('text_area_2')
                            : request()->input('api_url');

                        $form->hidden('options')->value($options);
                        $form->$formType('value', 'Value')->options($params);
                        break;
                    case 'file':
                    case 'image':
                    case 'multipleImage':
                    case 'multipleFile':
                        $form->$formType('value', 'Value')->withFormData(['form_type' => $formType]);
                        break;
                    case 'selectResource':
                        $callback = request()->input('callback');
                        $isMultiple = request()->input('is_multiple', 0);
                        $instance = $form
                            ->$formType('value', 'Value')
                            ->path(request()->input('api_url_for_select_resource'));
                        if ($isMultiple) {
                            $instance->multiple();
                        }
                        if ($callback && Str::contains('@', $callback)) {
                            list($class, $method) = explode('@', $callback);
                            $instance->options((new $class)->$method($item));
                        }

                        $options = request()->only(['callback', 'is_multiple', 'api_url_for_select_resource']);
                        $form->hidden('options')->value(json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                        break;
                    case 'tree':
                    case 'table':
                    case 'hasMany':
                        // TODO
                        break;
                    default:
                        $form->$formType('value', 'Value');
                        break;
                }
            }

            $form->saving(function (Form $form) use ($formType) {
                return $this->beforeSavingHook($formType, $form);
            });
        });
    }

    /**
     * 编辑配置
     *
     * @param  mixed  $id
     * @param  Content  $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description()['edit'] ?? trans('admin.edit'))
            ->body($this->editForm()->edit($id));
    }

    /**
     * 编辑配置提交操作
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response|null
     */
    public function update($id)
    {
        return $this->editForm()->update($id);
    }

    /**
     * 编辑配置表单
     *
     * @return Form
     */
    private function editForm()
    {
        return Form::make(new Setting(), function (Form $form) {
            $form->disableViewCheck();
            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewButton();;
            $form->text('name', '配置名称')->required();
            $form->text('key', 'Key')->required();

            $model = $form->model();
            $formType = $model->form_type;
            switch ($formType) {
                case 'map':
                case 'timeRange':
                case 'dateRange':
                case 'datetimeRange':
                case 'range':
                    $form->$formType('column_1', 'column_2', 'Value');
                    break;
                case 'time':
                case 'date':
                case 'datetime':
                    $form->hidden('options')->value($model->options);
                    $form->$formType('value', 'Value')->format($model->options)->value($model->value);
                    break;
                case 'checkbox':
                case 'radio':
                case 'slider':
                case 'listbox':
                    $options = json_decode($model->options, true);
                    $method = $formType == 'slider' || $formType == 'listbox' ? 'value' : 'checked';
                    $form->hidden('options')->value($model->options);
                    $form->$formType('value', 'Value')->options($options)->$method($model->value);
                    break;
                case 'file':
                case 'image':
                case 'multipleImage':
                case 'multipleFile':
                    $form->$formType('value', 'Value')->withFormData(['form_type' => $model->form_type])->value($model->value);
                    break;
                case 'select':
                case 'multipleSelect':
                case 'tags':
                    $form->hidden('options')->value($model->options);
                    $form->$formType('value', 'Value')->options(json_decode($model->options, true))->value($model->value);
                    break;
                case 'selectResource':
                    $callback = $model->options['callback'];
                    $instance = $form->$formType('value', 'Value')->path($model->options['api_url_for_select_resource']);
                    if ($model->options['is_multiple']) {
                        $instance->multiple();
                    }
                    if ($callback && Str::contains('@', $callback)) {
                        list($class, $method) = explode('@', $callback);
                        $instance->options((new $class)->$method($item));
                    }
                    $form->hidden('options')->value($model->options);
                    break;
                case 'tree':
                case 'table':
                case 'hasMany':
                    // TODO
                    break;
                default:
                    $form->$formType('value', 'Value')->value($model->value);
                    break;
            }

            $form->saving(function (Form $form) use ($formType) {
                return $this->beforeSavingHook($formType, $form);
            });
        });
    }

    /**
     * 表单事件
     *
     * @param $formType
     * @param $form
     * @return Form
     */
    private function beforeSavingHook($formType, &$form)
    {
        switch ($formType) {
            case 'map':
            case 'timeRange':
            case 'dateRange':
            case 'datetimeRange':
            case 'range':
                $form->value = [$form->column_1, $form->column_2];
                $form->deleteInput(['column_1', 'column_2']);
                break;
        }

        return $form;
    }
}
