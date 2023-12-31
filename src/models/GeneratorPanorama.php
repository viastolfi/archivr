<?php

class GeneratorPanorama{
  public static function generateHtml($panoramaName, $body, $firstView):string
  {
    $page = '
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>'.$panoramaName.'</title>
    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>
    <script src="https://unpkg.com/aframe-template-component@3.2.1/dist/aframe-template-component.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/c-frame/aframe-extras@7.1.0/dist/aframe-extras.min.js"></script>
    <script src="./scripts/functions.js"></script>
    <script src="./scripts/components.js"></script>
    <script src="./scripts/deviceHandler.js"></script>
    <script src="./scripts/smartphoneSliderComponent.js"></script>
    <script src="./scripts/computerSliderComponent.js"></script>
    
    <script src="scripts/menu.js"></script>
    <script src="scripts/slider.js"></script>
    <script src="scripts/pinchable.js"></script>
    <script src="scripts/colorChanged.js"></script>
    <script src="scripts/time-change.js"></script>
    <script src="scripts/button.js"></script>
    <script src="scripts/pressable.js"></script>
    <script src="scripts/event-manager.js"></script>
    <script src="scripts/pinchBar.js"></script>
    <script src="scripts/buttonVr.js"></script>
  </head>

  <body>
    <a-scene scene thumbstick-logging>

    <a-entity id="player" position="0 -1.6 0" rotation="' . strval($firstView->getCameraRotation()) . '">
      <!-- Caméra -->
      <a-camera cursor="rayOrigin: mouse" id="camera" wasd-controls="enabled: false"></a-camera>
    </a-entity>

      <a-entity id="base">
        '.$body.'
      </a-entity>
    </a-scene>
  </body>
</html>
      ';

    return $page;
  }

    public static function generateTimeline($timeline) : Template{
      $template = new Template();
      $body = '<div class="hud" id="div">';
      $vr_button = '<a-entity button-vr="';
      
      $classNumber = 1;
      foreach($timeline->getViews() as $view){
        $body .= '<button class="button-74" role="button" onclick="mobileOpacityHandler(\'class' . $classNumber . '\')" id="button' . $classNumber .'">' . $view->getDate() . '</button>';
        $vr_button .= 'class' . $classNumber . ': ' . $view->getDate() . ';';
        $classNumber++;
      }

      $vr_button .= '" rotation="-50 0 0" position="0.1 -0.6 -0.5"></a-entity>';

      $body .= "</div>";
      $body .= $vr_button;

      $classNumber = 1;
      foreach($timeline->getViews() as $view){
        if($classNumber == 1){
          $body .= '<a-sky src="./assets/images/'. $view->getPath() .'" class="class' . $classNumber . '" sliderelement></a-sky>';
        } else {
          $body .= '<a-sky src="./assets/images/'. $view->getPath() .'" class="class' . $classNumber . '" opacity="0.0" sliderelement></a-sky>';
        }

        $elementId = 1;

        foreach($view->getElements() as $element){
          if($classNumber == 1){
            $opacity = 'opacity="1"';
          } else {
            $opacity = 'visible="false"';
          }
          if(get_class($element) == Sign::class){
            $body .= '
              <a-entity position="'.strval($element->getPosition()).'" rotation="' . strval($element->getRotation()) . '" text="value: '.$element->getContent().'; align: center" animationcustom class="class' . $classNumber . '" ' . $opacity . ' ></a-entity>
            ';
          } elseif(get_class($element) == Waypoint::class)
          {
            $cameraRotation = '';
            if(get_class($element->getView()) == Timeline::class){
              $cameraRotation = strval($element->getView()->getFirstView()->getCameraRotation());
            } else {
              $cameraRotation = strval($element->getView()->getCameraRotation());
            }
            $path = explode('.', $element->getView()->getPath())[0].'.html';
          
            $body .= '
              <a-image src="./assets/images/right-arrow.png" position=" ' . $element->getPosition()->getPosition() . ' " rotation=" ' . $element->getRotation()->getRotation() . ' " id=" ' . $element->getId() . ' " scale=" ' . $element->getScale() . ' " onclick="goTo(\'templates/' . $path . '\', \'' . $cameraRotation . '\')"></a-image>
            ';
          } elseif(get_class($element) == AssetImported::class)
          {
            $animation = "";
            if($element->getAnimate()) {
              $animation = "animation-mixer";
            }
            $body .= '<a-entity id="' . $element->getId() . '" position="'.strval($element->getPosition()).'" rotation="' . strval($element->getRotation()) . '" scale="' . $element->getScale() .'" class="class' . $classNumber . '" ' . $opacity . '>
                      <a-entity gltf-model="./assets/models/'. $element->getPath() .'/'. $element->getModel().'" ' . $animation .'></a-entity>
                    </a-entity>';
          }
          $elementId += 1;
        }
        $classNumber++;
      }

      $template->body = $body;
      $template->name = $timeline->getName().".html";
      return $template;
    }

  public static function createDirectory($panorama, $fisrtView){
      $basePath = "./.datas/out";
      $folders = array('assets', 'assets/images', 'assets/sounds', '/scripts', '/templates', '/assets/models', 'assets/styles');
      $panoramaId = $panorama->getId();
      $firstViewBody = '';
      $firstViewObject = null;
      $elements = array();
      $panoramaModel = new PanoramaModel($panorama);

      // create the html of all the views templates
      foreach($panorama->getViews() as $view){
        if($panoramaModel->isMap()) {
          $template = self::generateBase($view, $panorama->getMap());
        } else {
          $template = self::generateBase($view);
        }
        array_push($elements, $template);
        if($template->name == explode('.', $fisrtView)[0].'.html'){
          $firstViewBody = $template->body;
        }
        if($view->getPath() == $fisrtView ){
          $firstViewObject = $view;
        }
      }

      // create the html of all the timelines templates
      foreach($panorama->getTimelines() as $key => $timeline) {
        $timelineModel = new TimelineModel($timeline);
        $template = self::generateTimeline($timeline);
        array_push($elements, $template);
        if($timeline->getId() == $fisrtView) {
          $firstViewBody = $template->body;
          $firstViewObject = $timelineModel->getFirstView();
        }
      }

      // generate the html of the index.html page
      $page = self::generateHtml($panorama->getName(), $firstViewBody, $firstViewObject);

      // get all the images added by the user
      $images = self::getImages($panorama);

      // recreate the out directory
      if(!file_exists($basePath)){
        mkdir($basePath);
      }else{
        Utils::delete_directory($basePath);
        mkdir($basePath);
      }

      foreach($folders as $folder){
        mkdir($basePath.'/'.$folder);
      }

      // create the index.html file
      touch($basePath.'/index.html');
      file_put_contents($basePath.'/index.html',$page);

      // copy all the necessary base file
      $files = scandir(".template");
      foreach($files as $file){
        if($file == "." or $file == ".."){
          continue;
        }
        if(is_dir('.template/'.$file)){
          $path = "";
          if($file == "direction_arrow") {
            $path = "/assets/models/";
          } elseif($file == "images") {
            $path = "/assets/";
          } else {
            $path = "/";
          }
          Utils::directory_copy('./.template/'.$file, $basePath.$path.$file);
        }
        if($file == "fiveserver.config.js"){
          copy('./.template/'.$file, $basePath.'/'.$file);
        }
      }

      // create all the template file
      foreach($elements as $element){
        touch($basePath.'/templates/'.$element->name);
        file_put_contents($basePath.'/templates/'.$element->name, $element->body);
      }

      // create the map
      if($panoramaModel->isMap()){
        $map = self::generateMap($panorama->getMap());
        touch($basePath.'/templates/'.$map->name);
        file_put_contents($basePath.'/templates/'.$map->name, $map->body);

        $data = file('./.datas/out/scripts/computerSliderComponent.js');
        $data[47] = 'goTo("./templates/'.$map->name.'","0 0 0")';
        $data[71] = 'goTo("./.templates/'.$map->name.'","0 0 0")';
        file_put_contents('./.datas/out/scripts/computerSliderComponent.js', $data);

        $data = file('./.datas/out/scripts/event-manager.js');
        $data[13] = 'goTo("./templates/'.$map->name.'","0 0 0")';
        file_put_contents('./.datas/out/scripts/event-manager.js', $data);
      } 

      // copy all the images in the out directory
      foreach($images as $image){
        if(is_dir('./.datas/' . $panoramaId . "/" . $image)) {
          Utils::directory_copy('./.datas/'.$panoramaId.'/'.$image, $basePath.'/assets/models/'.$image);
        } else {
          copy('./.datas/'.$panoramaId.'/'.$image, $basePath.'/assets/images/'.$image);
        }
      }
      
      copy('./.template/style.css','./.datas/out/assets/styles/style.css');

      // create the json file and generate the zip file
      self::createJsonFile($panorama);
      self::generateZip($panorama->getName());
    }

    public static function createJsonFile($panorama){
      $path = './.datas/out/.holder.json';
      $json = json_encode($panorama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      touch($path);
      file_put_contents($path, $json);
    }

    public static function generateZip($panoramaName){
      if(!file_exists('./.datas/zip')){
        mkdir('./.datas/zip');
      }

      // Get real path for our folder
      $rootPath = realpath('./.datas/out');
      $zipName = str_replace(' ','_', $panoramaName);

      // Initialize archive object
      $zip = new ZipArchive();
      $zip->open('./.datas/zip/'.$zipName.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

      // Create recursive directory iterator
      /** @var SplFileInfo[] $files */
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($rootPath),
          RecursiveIteratorIterator::LEAVES_ONLY
      );

      foreach ($files as $name => $file)
      {
          // Skip directories (they would be added automatically)
          if (!$file->isDir())
          {
              // Get real and relative path for current file
              $filePath = $file->getRealPath();
              $relativePath = substr($filePath, strlen($rootPath) + 1);

              // Add current file to archive
              $zip->addFile($filePath, $relativePath);
          }
      }

      // Zip archive will be created only after closing object
      $zip->close();
    }

    public static function getImages($panorama):array{
      $images = scandir('./.datas/'.$panorama->getId());
      $images = array_slice($images, 2, count($images));
      return $images;
    }

    public static function generateBase($view, $map = null):Template{
      $path = $view->getPath();
      $template = new Template();

      $body = '<a-sky id="skybox" src="./assets/images/'.$path.'" animationcustom></a-sky>';

      if($map != null) {
        $body .= '
        <div class="hud" id="div">
          <button class="button-74" role="button" onclick="goTo(\'templates/'. explode('.', $map->getPath())[0].'.html\')" id="buttonMap">Map</button>
        </div>
        ';
      }

      $elementId = 1;

      foreach($view->getElements() as $element){
        if(get_class($element) == Sign::class){
          $body .= '
            <a-entity position="'.strval($element->getPosition()).'" rotation="' . strval($element->getRotation()) . '" text="value: '.$element->getContent().'; align: center" animationcustom"></a-entity>
          ';
        }elseif(get_class($element) == Waypoint::class){
          $cameraRotation = '';

          if(method_exists($element->getView(), 'getPath')){
            $path = explode('.', $element->getView()->getPath())[0].'.html';
          } else {
            $path = $element->getView()->getName().'.html';
          }

          if(get_class($element->getView()) == Timeline::class){
            $viewModel = new TimelineModel($element->getView());
            $cameraRotation = strval($viewModel->getFirstView()->getCameraRotation());
          } else {
            $cameraRotation = strval($element->getView()->getCameraRotation());
          }
        
          $body .= '
            <a-image src="./assets/images/right-arrow.png" position=" ' . $element->getPosition()->getPosition() . ' " rotation=" ' . $element->getRotation()->getRotation() . ' " id=" ' . $element->getId() . ' " scale=" ' . $element->getScale() . '" onclick="goTo(\'templates/' . $path . '\', \'' . $cameraRotation . '\')"></a-image>
          ';
        } elseif(get_class($element) == AssetImported::class){
          $animation = "";
          if($element->getAnimate()) {
            $animation = "animation-mixer";
          } 
          $body .= '<a-entity id="' . $element->getId() . '" position="'.strval($element->getPosition()).'" rotation="' . strval($element->getRotation()) . '" scale="' . $element->getScale() .'">
                      <a-entity gltf-model="./assets/models/'. $element->getPath() .'/'. $element->getModel().'" ' . $animation . '></a-entity>
                    </a-entity>';
        }
        $elementId += 1;
      }

      $template->body = $body;
      $template->name = explode('.', $view->getPath())[0].'.html';

      return $template;
    }

  public static function generateMap($map):Template{
    $path = $map->getPath();
    $template = new Template();

    $body = '<a-sky id="skybox" src="assets/images/sky.png" class="classMap" animationcustom></a-sky>';

    $body .= '<a-image src="assets/images/' . $path . '" position="0 0 -0.6" width="2.1" class="classMap"></a-image>';

    foreach($map->getElements() as $element){
      $elementPath = explode('.', $element->getView()->getPath())[0].'.html';
      $element->getPosition()->setZ(-0.5);
      $body .= '
        <a-image class="classMap" onclick="goTo(\'templates/' . $elementPath . '\')" animationcustom  position="' . strval($element->getPosition()) . '" src="assets/images/blueWaypoint.png" color="#FFFFFF" rotation="0 90 0" look-at="#camera" height="0.1" width="0.1" map></a-image>
      ';
    }

    $template->name = explode('.', $path)[0] . '.html';
    $template->body = $body;

    return $template;
  }

  public static function loadFromFile($data){
    $panorama = new Panorama($data['name']);
    $panorama_images_array = array();
    $timelines_views_array = array();
    $timelines_array = array();
    $views_array = array();

    // view and timeline object creation
    if(isset($data['views'])){
      foreach($data['views'] as $view){
        $panorama_images_array[$view['path']]['object'] = new View($view['path']); 
        $panorama_images_array[$view['path']]['object']->setCameraRotation($view['cameraRotation']['y']);
        $panorama_images_array[$view['path']]['is_view'] = true;
      }
    }
    if(isset($data['timelines'])){
      foreach($data['timelines'] as $timeline){
        $panorama_images_array[$timeline['name']]['object'] = new Timeline($timeline['name']);
        foreach($timeline['views'] as $view) {
          $panorama_images_array[$timeline['name']][$view['path']] = new View($view['path']);
          $panorama_images_array[$timeline['name']][$view['path']]->setCameraRotation($view['cameraRotation']['y']);
          $panorama_images_array[$timeline['name']][$view['path']]->setDate($view['date']);
          array_push($timelines_views_array, $view);
        }
      }
    }

    $views = array_merge($data['views'], $timelines_views_array);

    // waypoint and sign creation
    foreach($views as $view){
      $array_element = array();
      foreach($view['elements'] as $element){
        $tmp = null;
        if(isset($element['destination'])){
          foreach($panorama_images_array as $key => $value){
            if($key == $element['destination']){
              $tmp = new Waypoint($value['object']);
              $tmp->set($element);
              break;
            }
          }
        } elseif(isset($element['model'])) 
        {
          $tmp = new AssetImported($element['path'], $element['model']);
          $tmp->set($element);
        } else 
        {
          $tmp = new Sign($element['content']);
          $tmp->set($element);
        }
        array_push($array_element, $tmp);
      }

      // set the data of each view with all the element
      $keys = array_keys($panorama_images_array);
      foreach($keys as $key){
        if($key == $view['path']){
          $image_model = new ImageModel($panorama_images_array[$key]['object']);
          foreach($array_element as $element) {
            $image_model->addElement($element);
          }
          array_push($views_array, $panorama_images_array[$key]['object']);
          continue;
        } else {
          if(isset($panorama_images_array[$key][$view['path']])){
            $image_model = new ImageModel($panorama_images_array[$key][$view['path']]);
            foreach($array_element as $element) { 
              $image_model->addElement($element);
            };
            $panorama_images_array[$key]['object']->set($panorama_images_array[$key][$view['path']]);
            if(!in_array($panorama_images_array[$timeline['name']]['object'], $timelines_array)){
              array_push($timelines_array, $panorama_images_array[$timeline['name']]['object']);
            }
            continue;
          }
        }
      }
    }

    // map creation
    if(isset($data['map'])) {
      $map = new Map($data['map']['path']);
      $waypoint_array = array();
      foreach($data['map']['elements'] as $element) {
        foreach($views_array as $view) {
          if($view->getPath() == $element['destination']) {
            $waypoint = new Waypoint($view);
            $waypoint_array[] = $waypoint;
            break;
          }
        }
      }
      $map->set($waypoint_array);
      $panorama->setMap($map);
    }

    $panorama->setViews($views_array);
    $panorama->setTimelines($timelines_array);
    $panorama->setId($data['id']);

    return $panorama;
  }
}