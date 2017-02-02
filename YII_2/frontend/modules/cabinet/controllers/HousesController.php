<?php

namespace app\modules\cabinet\controllers;

use Yii;
use common\models\Houses;
use common\models\Search\HousesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\controllers\AuthController;
use yii\helpers\BaseFileHelper;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Point;
use Imagine\Image\Box;
use yii\helpers\FileHelper;

/**
 * HousesController implements the CRUD actions for Houses model.
 */

//http://yii.loc/index.php?r=cabinet/houses

class HousesController extends AuthController
{
	public $layout = "inner";
    /**
     * @inheritdoc
     */
    /*public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }*/

    /**
     * Lists all Houses models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HousesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Houses model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Houses model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Houses();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //return $this->redirect(['view', 'id' => $model->id]);
			return $this->redirect(['step2', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

	public function actionStep2($id)
	{
		$model = $this->findModel($id);
		$images = [];
		//$images[] = '<img src="/uploads/houses/5/general/small.jpg" width=250>';
		//$images[] = '<img src="/uploads/houses/5/general/small.jpg" width=250>';
		if ($general_image = $model->general_image) {
			$images[] = '<img src="/uploads/houses/'.$id.
				'/general/small_'.$general_image.'" width=250>';
		}

		//if 		
		$path = Yii::getAlias("@frontend/web/uploads/houses/".$id);
		$images_add = [];
		//try
		{
			if (is_dir($path))
			{
				$files = FileHelper::findFiles($path);
				foreach ($files as $file)
				{
					if (strstr($file,"small_") && !strstr($file,"general"))
					{
						$images_add[] = '<img src="/uploads/houses/'.$id.
								'/'.basename($file).'" width=250>';
					}
				} 
			}
		}	
		
		return $this->render('step2', ['model' => $model, 
			'images' => $images, 'images_add' => $images_add]);	
	}

    /**
     * Updates an existing Houses model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Houses model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Houses model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Houses the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Houses::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

	public function actionFileUploadGeneral()
	{
		if (Yii::$app->request->post())
		{
			$id = Yii::$app->request->post("house_id");
			
			yii::trace("id:".$id,'mytest');
			
			$path = Yii::getAlias("@frontend/web/uploads/houses/".$id."/general");
			BaseFileHelper::createDirectory($path);
			
			$model = $this->findModel($id);
			//$model->scenario = 'step2';
/*			if (isset($model))
			yii::trace("model: yes",'mytest0');
			else yii::trace("model: no",'mytest0');
*/
			yii::trace("model-general:".$model->general_image,'mytest4');

			$file = UploadedFile::getInstance($model, 'general_image');

			if (!isset($file))
				return false;

			yii::trace("file:".$file,'mytest1');
			
			$name = 'general.'.$file->extension;
			$file->saveAs($path.DIRECTORY_SEPARATOR.$name);
			
			$image = $path.DIRECTORY_SEPARATOR.$name;
			$new_name = $path.DIRECTORY_SEPARATOR."small_".$name;
			
			yii::trace("old_name:".$image,'mytest2');
			yii::trace("new_name:".$new_name,'mytest2');
			
			$model->general_image = $name;
			$model->save();
			
			$size = getimagesize($image);
			$width = $size[0];
			$height = $size[1];
			
			Image::frame($image, 0, '666', 0)
				->crop(new Point(0, 0), new Box($width, $height))
				->resize(new Box(710, 484))
				->save($new_name, ['quality'=>100]);
				
			return true;
		}		
	}

	public function actionFileUploadImages()
	{
		if (Yii::$app->request->post())
		{
			$id = Yii::$app->request->post("house_id");
			$path = Yii::getAlias("@frontend/web/uploads/houses/".$id);
			BaseFileHelper::createDirectory($path);
			
			$file = UploadedFile::getInstanceByName('images');
			if (!isset($file))
				return false;

			$name = time().'.'.$file->extension;
			$file->saveAs($path.DIRECTORY_SEPARATOR.$name);
			
			$image = $path.DIRECTORY_SEPARATOR.$name;
			$new_name = $path.DIRECTORY_SEPARATOR."small_".$name;
			
			yii::trace("old_name:".$image,'mytest2');
			yii::trace("new_name:".$new_name,'mytest2');
			
			$size = getimagesize($image);
			$width = $size[0];
			$height = $size[1];
			
			Image::frame($image, 0, '666', 0)
				->crop(new Point(0, 0), new Box($width, $height))
				->resize(new Box(400, 273))
				->save($new_name, ['quality'=>100]);
			sleep(1);
				
			return true;
		}		
	}
}
