<?php
ob_start();
session_start();

header('Content-type: image/gif');

/**
 * Класс для построения диаграмм бубликов
*/
class Draw_arc {

  protected $x1 = 200;
  protected $y1 = 200;
  public $radius1 = 100;
  public $colors1;
  public $dataArray1;
  public $im1;
  public $white1;
  public $factor = 0.8;
  public $background_color;

  public function __construct($dataArray1){
    $this->dataArray1 = $dataArray1;
    $this->im1 = @ImageCreate (500, 400);   
    $this->background_color = imagecolorallocate($this->im1, 255, 255, 255);

    $this->colors1[8] = ImageColorAllocate ($this->im1, 0, 0, 0);
    $this->colors1[1] = ImageColorAllocate ($this->im1, 255, 0, 0);
    $this->colors1[7] = ImageColorAllocate ($this->im1, 0, 255, 0);
    $this->colors1[2] = ImageColorAllocate ($this->im1, 0, 0, 255);
    $this->colors1[3] = ImageColorAllocate ($this->im1, 255, 255, 0);
    $this->colors1[4] = ImageColorAllocate ($this->im1, 255, 0, 255);
    $this->colors1[5] = ImageColorAllocate ($this->im1, 0, 255, 255);
    $this->colors1[6] = ImageColorAllocate ($this->im1, 221, 221, 221);
    $this->white1 =     ImageColorAllocate ($this->im1, 255, 255, 255);
  }

  public function drawing_images(){
    ImagePNG($this->im1);  //Вывод PNG изображения в браузер или файл; $im - ресурс изображения
    imagedestroy($this->im1);  // Уничтожение изображения
  }

  private function drawSegment($x0,$y0,$radius,$begAngle,$endAngle,$color)
    {
    //рисуем сектор круга соответствующих размера и цвета
    imagefilledarc($this->im1, $x0, $y0, $radius*2, $radius*2,
        $begAngle, $endAngle,
        $color, IMG_ARC_PIE); 
    }

  public function drawDiagram()
    {
    $count=count($this->dataArray1);//вычисляем количество элементов в массиве данных
    //получаем сумму всех элементов массива
    $sumVal=array_sum($this->dataArray1);
    //начнем рисовать сектора с угла 0 градусов
    $begAngle=0;
    //вычисляем угол для отрисовки первого сектора
    $endAngle=floor($begAngle+(($this->dataArray1[1]*100)/$sumVal)*360/100);
    //рисуем сегмент, соответствующий величине первого элемента массива
    $this->drawSegment($this->x1,$this->y1,$this->radius1,$begAngle,$endAngle,$this->colors1[1]);
    //аналогично поступаем с остальными элементами массива, за исключением последнего
    for($i=2;$i<$count;$i++)
    {
        $begAngle=$endAngle;
        $endAngle=floor($begAngle+
            (($this->dataArray1[$i]*100)/$sumVal)*360/100);
            $this->drawSegment($this->x1,$this->y1,$this->radius1,
            $begAngle,$endAngle,$this->colors1[$i]);
    }
    //рисуем сегмент для последнего элемента массива
    $begAngle=$endAngle;
    $endAngle=360;
    $this->drawSegment($this->x1,$this->y1,$this->radius1,$begAngle,$endAngle,$this->colors1[$count]);
    //рисуем дырку от бублика
    $this->drawSegment($this->x1, $this->y1, $this->radius1*$this->factor, 0, 360, $this->white1);
    }
}

/**
 * Класс который будет использовать для построения вложенных дуг
*/
abstract class Decorator{
  protected $object;
  public $radius1;
  public function __construct($object)  {
    $this->object = $object;
    $object->radius1 *= 0.7;
  }
  protected function getObject()  {
    return $this->object;
  }
  public function drawDiagram()  {
    return $this->getObject()->drawDiagram();
  }
}

/**
 * Класс который будет использовать для построения вложенных дуг
*/
class AddArc extends Decorator{
  public function drawDiagram(){
    parent::drawDiagram();
  }
}

//в учебных целях будет два сектора
$_SESSION['data'][1]=10; 
$_SESSION['data'][2]=10; 

// в зависимости от того какой пришел режим планируем работу
$mode = $_GET['mode'];
switch( $mode ){
  case  'RandomizeData':
    // режим хаотичной смены размеров секторов
    for( $i = 1; $i <= count($_SESSION['data']); $i++ ){
      $_SESSION['data'][$i] = rand(1, 10);
    }
    break;
  case  'AddDataset':
    // режим добавление внутренних дуг
    if ( $_SESSION['numberArcs'] <= 5 ){
      if ( isset($_SESSION['numberArcs']) ) {
        $_SESSION['numberArcs'] += 1; 
      }
      else{
        $_SESSION['numberArcs'] = 0; 
      }
    }
    break;
  case  'RemoveDataset':
    // режим удаления внутренних дуг
    if (isset($_SESSION['numberArcs']) && !empty($_SESSION['numberArcs'])) {
      if ( $_SESSION['numberArcs'] >= 1 ){
        $_SESSION['numberArcs'] -= 1;
      }
    }
    break;
  case  'AddData':
    // режим добавление цветных секторов
    if ( count($_SESSION['data']) < 8 /*count($colors)*/  )
      $_SESSION['data'][] = rand(1, 10);
    break;
  case  'RemoveData':
    // режим удаление цветных секторов
    $tmp = count($_SESSION['data']);
    if ( $tmp > 2 )
      unset($_SESSION['data'][$tmp]);
    break;
}

//рисуем диаграмму
$imaga = new Draw_arc($_SESSION['data']);
$imaga->drawDiagram();

// страница прогрузилась добавляем внутренние дуги при помощи класса Декоратора
if (isset($_SESSION['numberArcs']) && !empty($_SESSION['numberArcs'])) {
  $numberArcs = $_SESSION['numberArcs'];
  for($i = 0; $i < $numberArcs; $i++ ){
    $imaga1 = new AddArc($imaga);
    $imaga1->drawDiagram();
  }
}

//вывод изображения в браузер
$imaga->drawing_images();

