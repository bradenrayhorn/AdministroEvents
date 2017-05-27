<?php

    use Symfony\Component\Yaml\Yaml;

    class EventsPlugin extends AdministroPlugin {

        var $dataFile, $files, $events;

        public function configLoaded() {
            // Set file locations
            $this->dataFile = $this->administro->rootDir . 'data/events/events.yaml';
            $this->files = $this->administro->rootDir . 'data/events/files/';
            // Create directory
            @mkdir($this->files, 0777, true);
            // Add event file viewer
            $this->administro->reservedRoutes['eventfile'] = 'plugins/Events/eventroute.php';
            // Add admin page
            $this->administro->adminPages['events'] =
                array('icon' => 'calendar', 'name' => 'Events', 'file' => 'plugins/Events/admin/events.php');
            // Add variable
            $this->administro->variables['events'] = $this->eventDisplay();
            // Add forms
            array_push($this->administro->forms, 'addevent', 'deleteevent');
        }

        public function eventDisplay($hideOld = true, $admin = false, $showYear = false) {
            $this->loadEvents();

            uasort($this->events, function($p1, $p2) {
                $c1 = new DateTime($p1['date']);
                $c2 = new DateTime($p2['date']);
                if($c1 < $c2) return -1;
                if($c1 == $c2) return 0;
                if($c1 > $c2) return 1;
            });

            $html = '';

            if($admin) {
                $deleteNonce = $this->administro->generateNonce('deleteevent');
            }

            foreach($this->events as $id => $event) {
                $date = new DateTime($event['date']);
                if($hideOld && new DateTime() > $date) continue;
                $month = $date->format('F');
                $day = $date->format('j');
                $year = '';
                if($showYear) {
                    $year = ', ' . $date->format('Y');
                }
                $name = $event['name'];
                $link = false;
                $delLink = '';
                if($admin) {
                    $delLink = ' <a href="' . $this->administro->baseDir . 'form/deleteevent?nonce=' . $deleteNonce;
                    $delLink .= '&event=' . $id . '"><i class="fa fa-times"></i></a>';
                }
                if($event['file'] !== false) {
                    $link = $this->administro->baseDir . 'eventfile/' . $event['file'];
                } else if($event['link'] !== false) {
                    $link = $event['link'];
                }
                if($link !== false) {
                    $html .= '<div class="event"><a href="' . $link . '"><span class="date">'
                        . $month . ' ' . $day . $year . ': </span>' . $name . '</a>' . $delLink . '</div>';
                } else {
                    $html .= '<div class="event"><span class="date">' . $month . ' ' . $day . $year . ': </span>' . $name . $delLink;
                    $html .= '</div>';
                }
            }

            return $html;
        }

        public function loadEvents() {
            // Make sure file exists
            if(!file_exists($this->dataFile)) {
                file_put_contents($this->dataFile, Yaml::dump(array()));
            }
            // Load events
            $this->events = Yaml::parse(file_get_contents($this->dataFile));
        }

        public function onCleanData() {
            // Load events
            $this->loadEvents();
            // Check for old events
            foreach($this->events as $id => $event) {
                if((new DateTime($event['date']))->modify('+1 day') < new DateTime()) {
                    unset($this->events[$id]);
                }
            }
            // Save new events
            file_put_contents($this->dataFile, Yaml::dump($this->events));
        }

    }

    function addeventform($administro) {
        $params = $administro->verifyParameters('addevent', array('name', 'day', 'month', 'year'));
        if($params !== false) {
            // Verify permission
            if($administro->hasPermission('admin.speakers')) {
                // Load events
                $administro->plugins['Events']->loadEvents();
                $events = $administro->plugins['Events']->events;
                // Generate event id
                $id = uniqid();
                while(isset($events[$id])) {
                    $id = uniqid();
                }
                // Generate the event
                $event = array(
                    'name' => $params['name'],
                    'date' => $params['year'] . '-' . $params['month'] . '-' . $params['day'],
                    'link' => false,
                    'file' => false
                );
                // Check if event has link or file
                if(isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
                    $file = $id . '.' . pathinfo($_FILES['file']['name'])['extension'];
                    $event['file'] = $file;
                    // Save the file
                    if ($_FILES['file']['size'] > 10000000) {
                        $administro->redirect('admin/events', 'bad/File must be under 10MB!');
                    }
                    move_uploaded_file($_FILES['file']['tmp_name'],
                        $administro->rootDir . 'data/events/files/' . $file);
                } else if(isset($_POST['link']) && !empty($_POST['link'])) {
                    $event['link'] = $_POST['link'];
                }
                // Write the event
                $events[$id] = $event;
                file_put_contents($administro->plugins['Events']->dataFile, Yaml::dump($events));
                $administro->redirect('admin/events', 'good/Added event!');
            } else {
                $administro->redirect('admin/home', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/events', 'bad/Invalid parameters!');
        }
    }

    function deleteeventform($administro) {
        $params = $administro->verifyParameters('deleteevent', array('event'), true, $_GET);
        if($params !== false) {
            if($administro->hasPermission('admin.event')) {
                $plugin = $administro->plugins['Events'];
                $plugin->loadEvents();
                unset($plugin->events[$params['event']]);
                file_put_contents($plugin->dataFile, Yaml::dump($plugin->events));
                $administro->redirect('admin/events', 'good/Deleted event!');
            } else {
                $administro->redirect('admin/events', 'bad/Invalid permission!');
            }
        } else {
            $administro->redirect('admin/events', 'bad/Invalid parameters!');
        }
    }
