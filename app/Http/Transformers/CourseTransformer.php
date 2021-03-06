<?php

namespace App\Http\Transformers;

use Illuminate\Http\Request;

use App\Models\Course;

class CourseTransformer extends BaseTransformer
{
    /**
     * Turn this item object into a generic array
     *
     * @param Course $course
     * @return array
     */
    public function transform(Course $course)
    {
        $data = [
            'self' => app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route('courses.show', $course->id),
            'title' => $course->title,
            'description' => $course->description,
            'campus' => null,
            'school' => null,
        ];

        // There's not always a campus
        if ($course->campus) {
            $data['campus'] = [
                'self' => app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route('campuses.show', $course->campus->id),
                'short' => $course->campus->short,
                'name' => $course->campus->name,
            ];
        } else {
            $data['school'] = $course->school;
        }

        if (isset($course->pivot)) {
            $pivot = [
                'from' => $course->pivot->from,
                'to' => $course->pivot->to,
            ];

            $data['self'] = app('Dingo\Api\Routing\UrlGenerator')
                ->version('v1')
                ->route('users.courses.show', [$course->pivot->user_id, $course->pivot->id]);

            $data = array_merge($data, $pivot);
        }

        return $data;
    }
}
