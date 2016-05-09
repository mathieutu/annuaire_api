<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Transformers\UserTransformer;

use App\Models\User;
use App\Models\Gadz;

class UserController extends Controller
{
    /**
     * List of relationships to load.
     *
     * @var array
     */
    private static $relationships = ['campus', 'gadz', 'photos', 'addresses', 'residences', 'courses',
        'degrees', 'responsibilities', 'jobs', 'socials'];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $users = User::with(self::$relationships)->paginate($request->input('items', 30))->appends($request->except('page'));

        return $this->response->paginator($users, new UserTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user)
    {
        return $this->response->item($user->load(self::$relationships), new UserTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function store(Request $request)
    {
        $fields = array_keys($request->all());

        if (!in_array('contact', $fields) or !in_array('promo', $fields)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("User must have `contact` and `promo` fields.");
        }

        // Prepare rules
        $rules = [
            // Contact
            'contact.firstname' => 'required|alpha',
            'contact.lastname' => 'required|alpha',
            'contact.gender' => 'in:m,f,null',
            'contact.mail' => 'required|email',
            'contact.phone' => 'required|string|regex:#^0[1-9][0-9]{8}$#',

            // Promo
            'promo.campus.id' => 'exists:campuses,id',
            'promo.year' => 'required|regex:#^20[0-9]{2}$#',
        ];

        if ($request->has('gadz')) {
            $gadz_rules = [
                'gadz.buque' => 'required|alpha',
                'gadz.fams' => 'required',
                'gadz.famsSearch' => 'required|regex:#^[0-9]{1,3}(,[0-9]{1,3})*$#',
            ];
            $rules = array_merge($rules, $gadz_rules);
        }

        // Validation
        $v = app('validator')->make($request->all(), $rules);

        if ($v->fails()) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Validation error(s) : ' . $v->errors());
        }

        // Regroupe les infos correctement
        $user_data = $request->input('contact');
        $user_data['campus_id'] = $request->input('promo.campus.id');
        $user_data['year'] = $request->input('promo.year');

        // Crée les objets et les sauvegarde en base
        app('db')->beginTransaction();

        $user = new User($user_data);
        $user->save();

        $gadz = new Gadz($request->input('gadz'));
        $user->gadz()->save($gadz);

        app('db')->commit();

        // Retourne le nouvel user
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function update(Request $request, User $user)
    {
        // Prepare rules
        $rules = [];

        // Démarre la transaction

        if ($request->has('contact')) {
            $rules = array_merge($rules, [
                'contact.firstname' => 'required|alpha',
                'contact.lastname' => 'required|alpha',
                'contact.gender' => 'in:m,f,null',
                'contact.mail' => 'required|email',
                'contact.phone' => 'required|string|regex:#^0[1-9][0-9]{8}$#',
            ]);

            $user_data = $request->input('contact');
        } else {
            $user_data = [];
        }

        if ($request->has('promo')) {
            $rules = array_merge($rules, [
                'promo.campus.id' => 'exists:campuses,id',
                'promo.year' => 'required|regex:#^20[0-9]{2}$#',
            ]);

            $user_data['campus_id'] = $request->input('promo.campus.id');
            $user_data['year'] = $request->input('promo.year');
        }

        if ($request->has('gadz')) {
            $rules = array_merge($rules, [
                'gadz.buque' => 'required|alpha',
                'gadz.fams' => 'required',
                'gadz.famsSearch' => 'required|regex:#^[0-9]{1,3}(,[0-9]{1,3})*$#',
            ]);
        }

        // Validation
        $v = app('validator')->make($request->all(), $rules);

        if ($v->fails()) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Validation error(s) : ' . $v->errors());
        }

        // Crée les objets et les sauvegarde en base
        app('db')->beginTransaction();

        if ($request->has('contact') or $request->has('promo')) {
            $user->update($user_data);
        }

        if ($request->has('gadz')) {
            $user->gadz->update($request->input('gadz'));
        }

        app('db')->commit();

        // Retourne le nouvel user
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     */
    public function destroy(User $user)
    {
        $user->delete();
    }
}
