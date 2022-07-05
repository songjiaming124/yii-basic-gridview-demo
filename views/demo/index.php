<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap4\Modal;
/** @var yii\web\View $this */

$this->title = 'Demo';
?>

<?php $form = ActiveForm::begin([
    'id' => 'search_from',
    'action' => ['index'],
    'method' => 'get',
    'enableClientScript' => false,
]); ?>
<div class="form-group form-inline well well-sm">
<?= $form->field($filterModel, 'id_op')->label("ID:")->dropDownList(
    ['=' => '=', '>=' => '>=', '>' => '>', '<=' => '<=', '<' => '<']
); ?>
<?= $form->field($filterModel, 'id')->label('');?>
<?= $form->field($filterModel, 'name')->label("Name:"); ?>
<?= $form->field($filterModel, 'code')->label("Code:"); ?>
<?= $form->field($filterModel, 't_status')->label("Status:")->dropDownList(
    [''=>'All', 'ok'=>'Ok', 'hold'=>'Hold']
);?>  
</div>  
<div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    <?= Html::button('ExportCSV', ['class' => 'btn btn-info exportcsv']) ?>
    <?= Html::resetButton('Cancel', ['class' => 'btn btn-warning']) ?>

</div>
<?php 
ActiveForm::end(); 
?>
<div id="alertBox">
    <div class="alert alert-danger" role="alert" style="display: none;"></div>
    <div class="alert alert-primary" role="alert" style="display: none;"></div>
</div>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $filterModel,
    'id' => 'grid',
    'columns' => [
        [
            //动作列yii\grid\ActionColumn
            //用于显示一些动作按钮，如每一行的更新、删除操作。
            'class' => 'yii\grid\CheckboxColumn',
            'name' => 'id',
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => $model->id];
            },
            'headerOptions' => ['width' => '30'],
            // 'footer' => '<input class="select-on-check-all" name="user_id_all" value="1" type="checkbox">全选',
            // 'footerOptions' => ['colspan' => 3],
        ],
        'id',
        'name',
        'code',
        't_status',
        // ...
    ],
    // 'showFooter' => true,
]);

$this->registerJs(  
   '$(document).ready(function () {
    function selectAlert(str)
    {
        if(str == "all")
        {
            var a_html = "<a href=\"#\" class=\"alert-link clear-select\">clear selection</a>.";
            $(".alert-primary").html("All contenversations in this search have been selected. "+ a_html).show();
        }
        else
        {
            var ids = $("#grid").yiiGridView("getSelectedRows");
            var a_html = "<a href=\"#\" class=\"alert-link select-check-all-match\">Select all contenversations that match this search</a>.";
            $(".alert-primary").html("All "+ ids.length +" contenversations on this page have been selected. "+ a_html).show();
        }

    }

    function autoAlert(tips, sec)
    {
        $(".alert-danger").html(tips).show();
        setTimeout(function(){
            $(".alert-danger").html("").hide();
        }, sec);  
    }

    //批量导出
    $(".select-on-check-all").change(function(){
        var check_all = $("#grid .select-on-check-all").get(0).checked;
        if(check_all)
        {
            setTimeout(function(){
                var check_value = $("#grid .select-on-check-all").get(0).value;
                selectAlert(check_value);
            }, 100);
            
        }
        else
        {
            $("#grid .select-on-check-all").val("1");
            $(".alert-primary").html("").hide();
        }

    });

    $("#alertBox").on("click", ".select-check-all-match", function(){
        $("#grid .select-on-check-all").val("all");
        selectAlert("all");
    });

    $("#alertBox").on("click", ".clear-select", function(){
        $("#grid .select-on-check-all").val("1");
        selectAlert("1");
    });

    $(".exportcsv").on("click", function(){
        var ids = $("#grid").yiiGridView("getSelectedRows");
        var check_all = $("#grid .select-on-check-all").get(0).checked;
        var check_value = $("#grid .select-on-check-all").get(0).value;
        if(check_all && check_value == "all")
        {
            var url_params = $("#search_from").serialize();
        }
        else
        {
            if(ids.length == 0)
            {
                autoAlert("Plase select suppplier", 3000);  
                return;
            }

            var url_params = "ids="+ ids.join(",");
        }

        window.open("export?"+url_params);
        return;
    });

    });'  
);  
?>
