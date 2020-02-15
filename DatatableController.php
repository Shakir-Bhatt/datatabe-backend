public function index(Request $request){

        if(request()->ajax()){
            $draw = $_REQUEST['draw'];
            $start = $_REQUEST['start'];
            $length = $_REQUEST['length'];

            $paymentFields = [
                'id',
                'razor_payment_id',
                'order_id',
                'customer_id',
                'amount',
                'payment_mode',
                'status',
                'created_at',
            ];

            $paymentsRaw = Payment::select($paymentFields)->with(['customer' => function($query){
                return $query->select('id', 'email');
            }]);

            $recordsFiltered = $recordsTotal = Payment::count();


            if (!empty($_REQUEST['search']['value'])) {
                $searchValue = trim($_REQUEST['search']['value']);
                $paymentsRaw->where(function ($query) use($paymentFields, $searchValue) {
                    foreach ($paymentFields as $column) {
                        $query->orWhere($column, "like", "$searchValue");
                    }
                });
                $apisearch = true;
            }

            if (!empty($_REQUEST['columns'][3]['search']['value'])) {
                $searchValueColumn = trim($_REQUEST['columns'][3]['search']['value']);
                switch ($searchValueColumn) {
                    case 'successfull':
                        $paymentsRaw->whereHas('order');
                        break;

                    case 'failed':
                        $paymentsRaw->doesntHave('order');
                        break;
                }
                $apisearch = true;
            }

            if(!empty($_REQUEST['order'][0]['dir'])){
                $dir = $_REQUEST['order'][0]['dir'];
                $columnNumber = $_REQUEST['order'][0]['column'];

                $columnOrderBy = "";
                switch ($columnNumber) {
                    case 0:
                        $columnOrderBy = "id";
                        break;
                    case 2:
                        $columnOrderBy = "order_id";
                        break;
                    case 4:
                        $columnOrderBy = "payment_mode";
                        break;
                    case 5:
                        $columnOrderBy = "status";
                        break;
                    case 6:
                        $columnOrderBy = "amount";
                        break;
                }
                if(!empty($columnOrderBy)){
                    $paymentsRaw->orderBy($columnOrderBy,$dir);

                }
                $apisearch = true;
            }
            if ($apisearch) {
                $recordsFiltered = $recordsTotal = $paymentsRaw->count();
            }
            $payments = $paymentsRaw
                            ->offset($start)
                            ->limit($length)
                            ->get();

            $results = [
                "draw" => intval($draw),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered
            ];

            if (count($payments) > 0) {
                foreach($payments as $payment) {

                    $created = strtotime($payment->created_at);
                    $finalResultData = [
                        '<a data-toggle="tooltip" data-placement="top" title="Click to view detail" data-original-title="Click to view detail" class="btn btn-outline-primary btn-sm" target="_blank" href="'.url('transactions/view-details', $payment->id).'">#'.$payment->id.'</a>',
                        $payment->razor_payment_id,
                        $payment->order_id ?: 'N/A',
                        ($payment->customer) ? $payment->customer->get_phone_number(): 'N/A',
                        ($payment->payment_mode) ? strtoupper($payment->payment_mode) : 'N/A',
                        $payment->status ?: 'N/A',
                        $payment->amount,
                        date('d-m-Y  h:i:s a', $created)
                    ];
                    $results['data'][] = $finalResultData;
                }
            } else {
                $results['data'] = array();
            }

            echo json_encode($results);
            exit;
        } else {
            return view('admin.list-transactions');
        }


    	$filter = $request->query('filter', 'all');
    	$payments = [];
    	if($filter === 'all'){
    		$payments = Payment::orderBy('created_at', 'DESC')->simplePaginate(50);
    	}elseif ($filter === 'successful_orders') {
    		$payments = Payment::whereHas('order')->orderBy('created_at', 'DESC')->simplePaginate(50);
    	}elseif ($filter === 'failed_orders') {
    		$payments = Payment::doesntHave('order')->orderBy('created_at', 'DESC')->simplePaginate(50);
    	}else{
    		$payments = Payment::orderBy('created_at', 'DESC')->simplePaginate(50);
    	}

    	return view('admin.list-transactions')->with(compact('payments'));
    }
