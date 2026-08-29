<?php

namespace Khan\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Khan\Forms\Facades\Forms;

class FormTestController extends Controller
{
    /**
     * Display the Multi-Entity Data Explorer & Query Dashboard.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $entity = $request->query('entity', 'fees');
        $selectedDate = $request->query('date', null);
        $dateColumn = $request->query('date_column', null);
        $studentId = $request->query('student_id', null);
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');
        $search = $request->query('search', null);
        $limit = (int) $request->query('limit', 10);

        $filters = [
            'student_id' => $studentId,
            'type' => $type,
            'status' => $status,
            'search' => $search,
        ];

        // Fetch latest entries for the chosen entity
        $result = Forms::getEntries($entity, $selectedDate, $limit, $dateColumn, $filters);
        $studentsList = Forms::getStudentsList(null, 100);

        $records = $result['data'] ?? [];
        $message = $result['message'] ?? "Showing latest entries for [{$entity}].";

        return view('forms::test', [
            'entity' => $entity,
            'selectedDate' => $selectedDate,
            'dateColumn' => $dateColumn,
            'studentId' => $studentId,
            'type' => $type,
            'status' => $status,
            'search' => $search,
            'limit' => $limit,
            'records' => $records,
            'students' => $studentsList,
            'message' => $message,
            'count' => $result['count'] ?? count($records),
            'targetTable' => $result['table'] ?? null,
        ]);
    }

    /**
     * Execute filter/search query on records and return JSON.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $entity = $request->input('entity', $request->query('entity', 'fees'));
        $date = $request->input('date', $request->query('date'));
        $dateColumn = $request->input('date_column', $request->query('date_column'));
        $studentId = $request->input('student_id', $request->query('student_id'));
        $type = $request->input('type', $request->query('type', 'all'));
        $status = $request->input('status', $request->query('status', 'all'));
        $search = $request->input('search', $request->query('search'));
        $limit = (int) ($request->input('limit') ?: ($request->query('limit') ?: 10));

        $filters = [
            'student_id' => $studentId,
            'type' => $type,
            'status' => $status,
            'search' => $search,
        ];

        $result = Forms::getEntries($entity, $date, $limit, $dateColumn, $filters);

        return response()->json($result);
    }

    /**
     * Get single record details by ID for viewing/editing.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): JsonResponse
    {
        $entity = $request->query('entity', $request->input('entity', 'fees'));
        $result = Forms::getEntryById($entity, $id);

        $status = ($result['passed'] ?? false) ? 200 : 404;

        return response()->json($result, $status);
    }

    /**
     * Update an entry by ID.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $entity = $request->input('entity', $request->query('entity', 'fees'));
        $data = $request->all();

        $result = Forms::updateEntry($entity, $id, $data);

        $status = ($result['passed'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }

    /**
     * Get list of students for student picker dropdown.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function students(Request $request): JsonResponse
    {
        $search = $request->query('q', $request->input('q', null));
        $students = Forms::getStudentsList($search, 100);

        return response()->json([
            'passed' => true,
            'data' => $students,
            'count' => count($students),
        ]);
    }

    /**
     * Delete a single entry by ID.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $entity = $request->input('entity', $request->query('entity', 'fees'));
        $result = Forms::deleteEntry($entity, $id);

        $status = ($result['passed'] ?? false) ? 200 : 404;

        return response()->json($result, $status);
    }

    /**
     * Delete multiple entries in bulk.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $entity = $request->input('entity', $request->query('entity', 'fees'));
        $ids = (array) $request->input('ids', []);
        $result = Forms::deleteBulkEntries($entity, $ids);

        $status = ($result['passed'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }
}
