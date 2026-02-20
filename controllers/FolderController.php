<?php

namespace app\controllers;

use app\models\Folder;
use app\models\FolderForm;
use Yii;
use yii\rest\ActiveController;

class FolderController extends ActiveController
{
    public $modelClass = Folder::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove default authenticator
        unset($behaviors['authenticator']);

        // CORS first
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        // add authenticator back correctly
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options'], // allow preflight
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate()
    {
        ['folder_name' => $folder_name, 'description' => $description, 'space_id' => $space_id] = Yii::$app->request->post();

        $model = new FolderForm();
        $model->folder_name = $folder_name;

        if ($model->validate() === false) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => $model->errors];
        }

        //This IS ActiveRecord model that saves data to DB
        $folder = new Folder();
        $folder->folder_name = $folder_name;
        $folder->description = $description;
        $folder->space_id = $space_id;
        $folder->save();

        return $folder;
    }
}
