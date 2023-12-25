<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use kartik\select2\Select2;

$this->title = Yii::t('imagemanager','Image manager');

?>
<div id="module-imagemanager" class="container-fluid <?=$selectType?>">
    <div class="row">
        <div class="col-xs-6 col-sm-10 col-image-editor">
            <div class="image-cropper">
                <div class="image-wrapper">
                    <img id="image-cropper" />
                </div>
                <br>
                <div class="cropper-aspect">
                    Poměr stran:
                    <div class="cropper-aspect-inputs">
                        <?=Html::textInput('input-imagemanager-aspect-width', null, ['id'=>'input-imagemanager-aspect-width', 'class'=>'form-control', 'placeholder'=>'16'])?>
                        <span>:</span>
                        <?=Html::textInput('input-imagemanager-aspect-height', null, ['id'=>'input-imagemanager-aspect-height', 'class'=>'form-control', 'placeholder'=>'9'])?>
                    </div>
                </div>
                <br>
                <div class="rotate-buttons">
                    <a href="#" class="btn btn-primary rotate-counter-clockwise">
                        <i class="glyphicon glyphicon-repeat" style="transform: scaleX(-1);"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Otočit o 90° (proti směru)')?></span>
                    </a>
                    <a href="#" class="btn btn-primary rotate-clockwise">
                        <i class="glyphicon glyphicon-repeat"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Otočit o 90° (po směru)')?></span>
                    </a>
                </div>
                <br>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary apply-crop">
                        <i class="fa fa-crop"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Crop')?></span>
                    </a>
                    <?php if($viewMode === "iframe"): ?>
                        <a href="#" class="btn btn-primary apply-crop-select">
                            <i class="fa fa-crop"></i>
                            <span class="hidden-xs"><?=Yii::t('imagemanager','Crop and select')?></span>
                        </a>
                    <?php endif; ?>
                    <a href="#" class="btn btn-default cancel-crop">
                        <i class="fa fa-undo"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Cancel')?></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-sm-10 col-data-editor">
            <div class="image-editor">
                <?php if(class_exists('common\models\ImagemanagerFolder')): ?>
                    <div class="form-group">
                        <label for="data_editor_alt"><?=Yii::t('imagemanager','Název (alt)')?></label>
                        <input type="text" class="form-control" name="data_editor_alt" id="data_editor_alt">
                    </div>
                    <div>
                        <?=
                        Select2::widget([
                            'name' => 'select-imagemanager-folder',
                            'data' => \yii\helpers\ArrayHelper::map(\common\models\ImagemanagerFolder::find()->all(), "ID", "name"),
                            'options' => [
                                'placeholder' => 'Vybrat složku',
                                'id' => 'select-imagemanager-folder'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ]
                        ]);
                        ?>
                        <br>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="data_editor_source"><?=Yii::t('imagemanager','Source')?></label>
                        <input type="text" class="form-control" name="data_editor_source" id="data_editor_source">
                    </div>
                <?php endif; ?>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary apply-edit">
                        <i class="fa fa-edit"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Save')?></span>
                    </a>
                    <?php if($viewMode === "iframe"): ?>
                        <a href="#" class="btn btn-primary apply-edit-select">
                            <i class="fa fa-edit"></i>
                            <span class="hidden-xs"><?=Yii::t('imagemanager','Save and select')?></span>
                        </a>
                    <?php endif; ?>
                    <a href="#" class="btn btn-default cancel-edit">
                        <i class="fa fa-undo"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Cancel')?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php if(class_exists('common\models\ImagemanagerFolder')): ?>
            <input type="hidden" id="folder" value="<?= $searchModel->folder; ?>">
            <div class="col-xs-6 col-sm-2 col-tree">
                <div class="form-group">
                    <?=Html::textInput('input-imagemanager-folder', null, ['id'=>'input-imagemanager-folder', 'class'=>'form-control', 'placeholder'=>'Jméno složky'])?>
                </div>
                <a class="btn btn-primary btn-block add-folder" href="">Přidat složku</a>
                <?php if($searchModel->folder): ?>
                    <a class="btn btn-danger btn-block delete-folder" href="">Odstranit složku</a>
                <?php endif; ?>
                <h3>Složky</h3>
                <?php
                $folders = \common\models\ImagemanagerFolder::find()->where(['parent_ID' => 0])->andWhere(['AND', ['!=', 'name', 'Faktury'], ['!=', 'name', 'Dobropisy']])->all();

                function showChildren($folder, $active, $viewMode, $inputFieldId){
                    $html = "";
                    if($folder->children){
                        foreach($folder->children as $child){
                            $html .= '<ul>';
                            $html .= '<li>';
                            $class = "";
                            if($active == $child->ID)
                                $class = 'active';
                            $html .= '<a class="' . $class . '" href="' . Url::to(['manager/index', 'ImageManagerSearch[folder]' => $child->ID, 'view-mode' => $viewMode, 'input-id' => $inputFieldId]) . '">' . $child->name . ' ('.  $folder->getFiles()->count() . ')</a>';
                            $html .= showChildren($child, $active, $viewMode, $inputFieldId);
                            $html .= '</li>';
                            $html .= '</ul>';
                        }
                        return $html;
                    }else{
                        return "";
                    }
                }
                ?>
                <p><a class="tree-all" href="<?= Url::to(['manager/index', 'view-mode' => $viewMode, 'input-id' => $inputFieldId]); ?>">Zobrazit vše (<?= \denobraz\imagemanager\models\ImageManager::find()->count(); ?>)</a></p>
                <ul class="imagemanager-tree">
                    <?php foreach($folders as $folder): ?>
                        <li>
                            <a class="<?php if((int) $searchModel->folder == $folder->ID): ?>active<?php endif; ?>" href="<?= Url::to(['manager/index', 'ImageManagerSearch[folder]' => $folder->ID, 'view-mode' => $viewMode, 'input-id' => $inputFieldId]); ?>"><?= $folder->name; ?> (<?= $folder->getFiles()->count(); ?>)</a>
                            <?= showChildren($folder, $searchModel->folder, $viewMode, $inputFieldId); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="col-xs-6 <?php if(class_exists('common\models\ImagemanagerFolder')): ?>col-sm-8<?php else: ?>col-sm-10<?php endif; ?> col-overview">
            <?php Pjax::begin([
                'id'=>'pjax-mediamanager',
                'timeout'=>'5000'
            ]); ?>
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemOptions' => ['class' => 'item img-thumbnail'],
                'layout' => "<div class='item-overview'>{items}</div> {pager}",
                'itemView' => function ($model, $key, $index, $widget) {
                    return $this->render("_item", ['model' => $model]);
                },
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
        <div class="col-xs-6 col-sm-2 col-options">
            <div class="form-group">
                <?=Html::textInput('input-mediamanager-search', null, ['id'=>'input-mediamanager-search', 'class'=>'form-control', 'placeholder'=>Yii::t('imagemanager','Search').'...'])?>
            </div>

            <?php
            if (Yii::$app->controller->module->canUploadImage):
                ?>

                <?=FileInput::widget([
                'name' => 'imagemanagerFiles[]',
                'id' => 'imagemanager-files',
                'options' => [
                    'multiple' => true,
                    'accept' => 'image/*'
                ],
                'pluginOptions' => [
                    'uploadUrl' => Url::to(['manager/upload']),
                    'uploadExtraData' => [
                        'folder_id' => isset(Yii::$app->request->get('ImageManagerSearch')['folder']) ? Yii::$app->request->get('ImageManagerSearch')['folder'] : '',
                    ],
                    'allowedFileExtensions' => \Yii::$app->controller->module->allowedFileExtensions,
                    'uploadAsync' => false,
                    'showPreview' => false,
                    'showRemove' => false,
                    'showUpload' => false,
                    'showCancel' => false,
                    'browseClass' => 'btn btn-primary btn-block',
                    'browseIcon' => '<i class="fa fa-upload"></i> ',
                    'browseLabel' => Yii::t('imagemanager','Upload')
                ],
                'pluginEvents' => [
                    "filebatchselected" => "function(event, files){  $('.msg-invalid-file-extension').addClass('hide'); $(this).fileinput('upload'); }",
                    "filebatchuploadsuccess" => "function(event, data, previewId, index) {
						imageManagerModule.uploadSuccess(data.jqXHR.responseJSON.imagemanagerFiles);
					}",
                    "fileuploaderror" => "function(event, data) { $('.msg-invalid-file-extension').removeClass('hide'); }",
                ],
            ]) ?>

            <?php
            endif;
            ?>

            <div class="image-info hide">
                <div class="thumbnail">
                    <img src="#">
                </div>
                <div class="edit-buttons">
                    <a href="#" class="btn btn-primary btn-block crop-image-item">
                        <i class="fa fa-crop"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Crop')?></span>
                    </a>
                    <a href="#" class="btn btn-primary btn-block edit-image-item">
                        <i class="fa fa-edit"></i>
                        <span class="hidden-xs"><?=Yii::t('imagemanager','Edit')?></span>
                    </a>
                </div>
                <div class="details">
                    <div class="fileName"></div>
                    <?php if(class_exists('common\models\ImagemanagerFolder')): ?>
                        <div class="alt"></div>
                    <?php endif; ?>
                    <div class="created"></div>
                    <div class="fileSize"></div>
                    <div class="dimensions"><span class="dimension-width"></span> &times; <span class="dimension-height"></span></div>
                    <a class="btn btn-primary btn-xs download-image-item" target="_blank" download href="#"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> <?=Yii::t('imagemanager','Download')?></a>
                    <?php
                    if (Yii::$app->controller->module->canRemoveImage):
                        ?>
                        <a href="#" class="btn btn-xs btn-danger delete-image-item" ><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> <?=Yii::t('imagemanager','Delete')?></a>
                    <?php
                    endif;
                    ?>
                </div>
                <?php if($viewMode === "iframe"): ?>
                    <a href="#" class="btn btn-primary btn-block pick-image-item"><?=Yii::t('imagemanager','Select')?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>  