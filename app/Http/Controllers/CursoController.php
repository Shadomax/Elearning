<?php

namespace App\Http\Controllers;

use App\Model\Curso;
use App\Model\Semana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CursoController extends Controller
{

    /**
     * Return view with the courses data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function courses()
    {
        return view('courses.courses',
            ['courses' => (Auth::user()->role->role > 2)
                ? $this->list()->where('estado', 'equal', true)
                : $this->list()]);
    }


    /**
     * Return view for a specific course.
     * @param $id
     * @return mixed
     */
    public function course($id)
    {
        return view('courses.course',
            ['course' => $this->find($id),
                'weeks' => Semana::all()->where('curso', '=', $id),
            ]);

    }

    /**
     * Return view forn adding a new course.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function new()
    {
        return view('courses.newCourse');
    }


    /**
     * Return all courses.
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function list()
    {
        return Curso::all();
    }

    /**
     * Return a single course.
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return Curso::find($id);
    }

    /**
     * Create a new course.
     * @param Request $request
     */
    public function insert(Request $request)
    {
        $this->validator($request->all())->validate();
        $course = $this->create($request->all());

        $this->createWeeks($course);

        return redirect('/courses');
    }

    public function updateView($id)
    {
        return view('courses.update', ['course' => Curso::find($id)]);
    }

    /**
     * Update a course information.
     * @param Request $request
     */
    public function update(Request $request)
    {
//        dd($request->all());
        $course = Curso::find($request->id);

        $course->nombre = $request->name;
        $course->descripcion = $request->description;
        $course->estado = ($request->state == 1);

        $course->save();

        return redirect('/courses');
    }


    /**
     * Validate the data comming from the client.
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:30',
            'initial_date' => 'required|date',
            'final_date' => 'required|date',
            'description' => 'required|max:2000|',
        ]);
    }

    /**
     * Save the new Course.
     * @param array $data
     * @return mixed
     */
    protected function create(array $data)
    {
        $initial = strtotime($data['initial_date']);
        $final = strtotime($data['final_date']);
        return Curso::create([
            'nombre' => $data['name'],
            'fecha_inicio' => date('Y-m-d', $initial),
            'fecha_final' => date('Y-m-d', $final),
            'descripcion' => $data['description'],
            'duracion' => $this->datediffInWeeks($initial, $final),
            'estado' => true,
        ]);
    }

    /**
     * Return the difference in weeks between two dates.
     * @param $date1
     * @param $date2
     * @return float
     */
    private function datediffInWeeks($date1, $date2)
    {
        if ($date1 > $date2)
            return $this->datediffInWeeks($date2, $date1);
        return floor(($date2 - $date1) / (60 * 60 * 24 * 7));
    }

    /**
     * Generate number of weeks for a new course.
     * @param $course
     */
    protected function createWeeks($course)
    {
        for ($i = 0; $i < $course->duracion; $i++) {
            Semana::create([
                'tema' => 'Semana ' . ($i+1),
                'visible' => true,
                'estado' => true,
                'curso' => $course->id_curso,
            ]);
        }
    }

}
