<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;

use App\Events\UserRoleChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use APP\Models\DeviceInformation;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Repositories\DeviceInformationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Prettus\Validator\Exceptions\ValidatorException;

class UserAPIController extends Controller
{
    private $userRepository;
    private $uploadRepository;
    private $roleRepository;
    private $customFieldRepository;
    private $deviceinfoRepository;
    private $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, UploadRepository $uploadRepository, RoleRepository $roleRepository, CustomFieldRepository $customFieldRepo, DeviceInformationRepository $deviceinfoRepo, OrderRepository $orderRepo)
    {
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->roleRepository = $roleRepository;
        $this->customFieldRepository = $customFieldRepo;
        $this->deviceinfoRepository = $deviceinfoRepo;
        $this->orderRepository = $orderRepo;
    }

    function checkuser(Request $request)
    {   
        $this->validate($request, [
            'email' => 'required'
        ]);
        $loginId = $request->input('email');
        $contains = strpos($loginId, '@');
        $field = 'email';
        if(!$contains){
            $field = 'mobile';
        }
        $user = $this->userRepository->findByField($field, $request->input('email'))->first();

        if (!$user) {
            return $this->sendError('User not found', 401);
        }

        return $this->sendResponse($user, 'User retrieved successfully');

    }
    
    function order_verification_code(Request $request)
    {   
        
        $device_verification_code = $request->input('order_verification_code');
        $order_id = $request->input('order_id');
        
        
        $code_from_db = $this->orderRepository->findByField('id', $order_id)->first();

        if (!$code_from_db || !($code_from_db->order_verification_code==$device_verification_code)) {
            return $this->sendError('Order not found', 401);
        }
        
        return $this->sendResponse($code_from_db, 'Order retrieved successfully');

    }

    function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                // Authentication passed...
                $user = auth()->user();
                if (!$user->hasRole('driver')) {
                    $this->sendError('User not driver', 401);
                }
                $user->device_token = $request->input('device_token', '');
                $user->save();
                return $this->sendResponse($user, 'User retrieved successfully');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }

    }
    
    function deviceinformation(Request $request)
    {
        
       try{ 
       $deviceinfo = new DeviceInformation;
            $deviceinfo->user_id = $request->input('user_id');
            $deviceinfo->device_type = $request->input('device_type');
            $deviceinfo->model = $request->input('model');
            $deviceinfo->manufacture = $request->input('manufacture');
            $deviceinfo->os_version = $request->input('os_version');
            $deviceinfo->screen_height = $request->input('screen_height');
            $deviceinfo->screen_width = $request->input('screen_width');
            $deviceinfo->brand = $request->input('brand');
            $deviceinfo->save();
       } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }


        return $this->sendResponse($deviceinfo, 'Device Information stored successfully');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    function register(Request $request)
    {
        try {
            $validationArry = array();
            if($request->input("is_social")=="YES"){
                $validationArry = [
                    'name' => 'required',
                    'email' => 'required|email',
                    'is_social' => 'required'
                ];
            }else{
                $validationArry = [
                    'name' => 'required',
                    'email' => 'required|email',
                    'mobile' => 'required',
                    'is_social' => 'required'
                ];
            }
            $this->validate($request, $validationArry);
            
            $emailCheck = $this->userRepository->findByField('email', $request->input('email'))->first();
            if($request->input("is_social")=="NO") {
                
                $mobileCheck = $this->userRepository->findByField('mobile', $request->input('mobile'))->first();
                
    
                if ($mobileCheck && !$emailCheck) {
                    return $this->sendError('Mobile already exists', 412);
                }
                
    
                if ($mobileCheck && $emailCheck) {
                    return $this->sendError('Mobile and Email already exists', 412);
                }
            }
    
            if ($emailCheck) {
                return $this->sendError('Email already exists', 412);
            }

            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->mobile = $request->input('mobile');
            $user->is_social = $request->input('is_social');
            $user->device_token = $request->input('device_token', '');
            $user->password = Hash::make($request->input('mobile'));
            $user->api_token = str_random(60);
            $user->uid = $request->input('uid');
            $user->assignRole('driver');
            $user->role = $request->input('role');
            $user->save();

            

            event(new UserRoleChangedEvent($user));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }


        return $this->sendResponse($user, 'User retrieved successfully');
    }

    function logout(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();
        if (!$user) {
            return $this->sendError('User not found', 401);
        }
        try {
            auth()->logout();
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }
        return $this->sendResponse($user['name'], 'User logout successfully');

    }

    function user(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();

        if (!$user) {
            return $this->sendError('User not found', 401);
        }

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    function settings(Request $request)
    {
        $settings = setting()->all();
        $settings = array_intersect_key($settings,
            [
                'default_tax' => '',
                'default_currency' => '',
                'default_currency_decimal_digits' => '',
                'app_name' => '',
                'currency_right' => '',
                'enable_paypal' => '',
                'enable_stripe' => '',
                'enable_razorpay' => '',
                'main_color' => '',
                'main_dark_color' => '',
                'second_color' => '',
                'second_dark_color' => '',
                'accent_color' => '',
                'accent_dark_color' => '',
                'scaffold_dark_color' => '',
                'scaffold_color' => '',
                'google_maps_key' => '',
                'fcm_key' => '',
                'mobile_language' => '',
                'app_version' => '',
                'enable_version' => '',
                'distance_unit' => '',
            ]
        );

        if (!$settings) {
            return $this->sendError('Settings not found', 401);
        }

        return $this->sendResponse($settings, 'Settings retrieved successfully');
    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param Request $request
     *
     */
    public function update($id, Request $request)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            return $this->sendResponse([
                'error' => true,
                'code' => 404,
            ], 'User not found');
        }
        $input = $request->except(['password', 'api_token']);
        try {
            if ($request->has('device_token')) {
                $user = $this->userRepository->update($request->only('device_token'), $id);
            } else {
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
                $user = $this->userRepository->update($input, $id);

                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $user->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage(), 401);
        }

        return $this->sendResponse($user, __('lang.updated_successfully', ['operator' => __('lang.user')]));
    }

    function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return $this->sendResponse(true, 'Reset link was sent successfully');
        } else {
            return $this->sendError([
                'error' => 'Reset link not sent',
                'code' => 401,
            ], 'Reset link not sent');
        }

    }
}
