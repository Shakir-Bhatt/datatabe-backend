public function index(){
        if(request()->ajax()){
            $draw = $_REQUEST['draw'];
            $start = $_REQUEST['start'];
            $length = $_REQUEST['length'];

            $condition = "";
            $columns = ['id','name','status','created_at'];
            $apisearch = false;

            if(!empty($_REQUEST['search']['value'])){
                $searchValue = trim($_REQUEST['search']['value']);
                $condition = "WHERE";
                foreach($columns as $column){
                    $condition .= " $column LIKE '%$searchValue%' OR ";
                }
                $condition = rtrim($condition,'OR ');
                $apisearch = true;
            }

            if(!empty($_REQUEST['order'][0]['dir'])){
                $orderBY = $_REQUEST['order'][0]['dir'];
                $columnNumber = $_REQUEST['order'][0]['column'];

                switch ($columnNumber) {
                    case 0:
                        $condition .=" ORDER BY id $orderBY ";
                        break;
                    case 1:
                        $condition .=" ORDER BY name $orderBY ";
                        break;
                    case 2:
                        $condition .=" ORDER BY created_at $orderBY ";
                        break;
                    case 3:
                        $condition .=" ORDER BY status $orderBY ";
                        break;
                }

                $apisearch = true;
            }

            $recordsTotal = $recordsFiltered = 0;
            if($apisearch) {
                $plans = DB::select("SELECT id FROM `plans` $condition");
                $recordsTotal = $recordsFiltered = count($plans);
            }

            $finalArray = [
                "draw" => intval($draw),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered
            ];

            if($recordsTotal>0) {
                $condition .=" limit $length  offset $start ";
                $plans = DB::select("SELECT * FROM `plans` $condition ");
                foreach ($plans as $plan) {
                    $planId = '<a class="btn btn-outline-primary btn-sm" title="Click to view plan details" target="_blank" href="'. url("plans/view-details",$plan->id).'">'.$plan->id.'</a>';

                    $name = $plan->name;
                    $createdAt = $plan->created_at;
                    $class = ($plan->status) ? 'success' : 'danger';
                    $status = ($plan->status) ? 'Active' : 'Inactive';
                    $status = '<span class="badge badge-'.$class.'">'.$status.'</span>';
                    $edit = '<a target="_blank" href="'. url("plans/edit",$plan->id).'">
                            <i class="nav-icon i-Pen-4 font-weight-bold"></i>
                        </a>';
                    $actionValue = ($plan->status) ? 'Enable':'Disable';
                    $actionStatus = ($plan->status) ? 'checked':'';

                    $action = '<label class="switch pr-5 switch-success mr-3">
                                    <span>'.$actionValue.'</span>
                                    <input class="plan-action" data-id="'.$plan->id.'" type="checkbox" '.$actionStatus.' onclick="togglePlan(this,'.$plan->id.');">
                                    <span class="slider"></span>
                                </label>';
                    $finalArray['data'][] = array($planId, $name, $createdAt, $status, $edit,$action);
                }
            }else {
                $finalArray['data'] = array();
            }
            echo json_encode($finalArray);
        } else {
            return view('admin.list-plans');
        }
    }
