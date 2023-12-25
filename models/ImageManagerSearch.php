<?php

namespace denobraz\imagemanager\models;

use common\models\ImagemanagerFolder;
use common\models\ImagemanagerFolderFile;
use denobraz\imagemanager\models\ImageManager;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use denobraz\imagemanager\Module;

/**
 * ImageManagerSearch represents the model behind the search form about `common\modules\imagemanager\models\ImageManager`.
 */
class ImageManagerSearch extends ImageManager
{
    public $globalSearch, $folder;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['globalSearch', 'folder'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ImageManager::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 100,
            ],
            'sort'=> ['defaultOrder' => ['created'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Get the module instance
        $module = Module::getInstance();

        if ($module->setBlameableBehavior) {
            $query->andWhere(['createdBy' => Yii::$app->user->id]);
        }

        $query->orFilterWhere(['like', 'fileName', $this->globalSearch])
            ->orFilterWhere(['like', 'created', $this->globalSearch])
            ->orFilterWhere(['like', 'modified', $this->globalSearch]);

        if(class_exists('common\models\ImagemanagerFolder')){
            if($this->globalSearch){
                $query->orFilterWhere(['like', 'alt', $this->globalSearch]);
            }
            if($this->folder){
                $query->innerJoin(ImagemanagerFolderFile::tableName(), ImagemanagerFolderFile::tableName().".file_ID=" . ImageManager::tableName().".id")
                    ->andFilterWhere([ImagemanagerFolderFile::tableName().".folder_ID" => $this->folder]);
            }
        }


        return $dataProvider;
    }
}
