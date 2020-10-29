<?php 
declare(strict_types = 1); 
namespace Gnezdilov\CurrencyUpdater\Controller\Index;

use Gnezdilov\CurrencyUpdater\Model\CurrencyModel;

class Index extends \Magento\Framework\App\Action\Action
{
    private $model;

	public function __construct(\Magento\Framework\App\Action\Context $context)
	{
	
		$this->model = new CurrencyModel();
		return parent::__construct($context);
	}

    public function execute()
    {
		print_r($this->actionUpdateCurrencies());
		exit;
    }


    public function actionUpdateCurrencies()
    {
        (array) $data = null;
        (array) $data['messages'] = [];
        (bool) $data['error'] = false;
        (int) $data['code'] = 1;
        (array) $data['data'] = '';
        (array) $headers = [];
        (int) $headers['status'] = 200;

        if(!$this->model->getIsStrict()){
            $data['messages']['warning'][] = "Be aware! Currencies will be updated as errors occur."; 
        }

        if ($this->model->isCurrencyServerAviable()) {
            if($this->model->isUpdateAbleSuccess()){
                $this->model->updateAll();
                $data['code'] = 2;
                $data['messages']['notifications'][] = "Currencies was success updated";                
            }else{
                $data['code'] = 3;
                foreach ($this->model->checkUpdateAbleSuccess()->messages as $type => $value) {
                    $data['messages'][$type][] = $value;
                }
            }

        } else {
            $data['code'] = 4;
            $data['messages']['warning'][] = "Currency server not ready.";
        }

        return  (json_encode($data));
    }
}
