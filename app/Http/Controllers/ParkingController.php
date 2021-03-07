<?php

namespace App\Http\Controllers;

use App\Constants\GoogleMapsError;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

// ParkingController.php
class ParkingController extends Controller
{
    // === validator
    private $rules = [
        'query'      => 'required',
    ];
    private $messages = [
        'required' => ':attribute is required',
    ];

    /**
     * Get a JSON parkings list
     * Request object may have a query string parameter 'query'
     *
     * @param Request $request
     * @return response()->json($response, HTTP_STATUS)
     */
    public function browse(Request $request)
    {
        // === Query validation
        $validator = Validator::make($request->all(), $this->rules, $this->messages );

        // if the query validation fail
        if ($validator->fails()) {
            return response()->json(
                ['error_message' => '400 : BAD REQUEST', 'errors' => $validator->errors()]
                , Response::HTTP_BAD_REQUEST);
        }

        // else get the google map api
        $query =  $validator->validate()['query'];

        //@https://github.com/alexpechkarev/google-maps
        $response = \GoogleMaps::load('textsearch')
                ->setParamByKey('query', 'parking+in+'.$query)
                ->setParamByKey('type', 'parking')
                ->get();

       $response = json_decode($response);

        // === Responses
        if ($response->status === GoogleMapsError::OK) {
            return response()->json(
                [$response]
                , Response::HTTP_OK);
        }
        if ($response->status === GoogleMapsError::ZERO_RESULTS) {
            return response()->json(
                [Response::HTTP_NOT_FOUND => 'NO PARKINGS FOUND']
                , Response::HTTP_NOT_FOUND);
        }
        if ($response->status === GoogleMapsError::OVER_QUERY_LIMIT) {
            return response()->json(
                [Response::HTTP_TOO_MANY_REQUESTS => GoogleMapsError::OVER_QUERY_LIMIT]
                , Response::HTTP_TOO_MANY_REQUESTS);
        }
        if ($response->status === GoogleMapsError::REQUEST_DENIED) {
            return response()->json(
                [Response::HTTP_UNAUTHORIZED => GoogleMapsError::REQUEST_DENIED]
                , Response::HTTP_UNAUTHORIZED);
        }
        if ($response->status === GoogleMapsError::INVALID_REQUEST) {
            return response()->json(
                [Response::HTTP_BAD_REQUEST => GoogleMapsError::INVALID_REQUEST]
                , Response::HTTP_BAD_REQUEST);
        }
        if ($response->status === GoogleMapsError::UNKNOWN_ERROR) {
            return response()->json(
                [Response::HTTP_INTERNAL_SERVER_ERROR => GoogleMapsError::UNKNOWN_ERROR]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
