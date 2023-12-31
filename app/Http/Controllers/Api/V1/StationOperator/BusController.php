<?php

namespace App\Http\Controllers\Api\V1\StationOperator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StationOperator\AddDriverToTripRequest;
use App\Http\Requests\StationOperator\GetBusRequest;
use App\Http\Requests\StationOperator\StoreBusRequest;
use App\Models\Bus;
use App\Models\BusStation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @group Bus Management
     *
     * Get a list of buses.
     *
     * This endpoint allows you to retrieve a list of buses associated with the authenticated user's bus station.
     *
     * @authenticated
     *
     * @queryParam search string The search query. (e.g., "ABC123")
     * @queryParam search_by string The field to search by. Possible values: "license_plate", "model", "owner_name".
     * @queryParam order_by string The field to order by. Possible values: "asc", "desc".
     *
     * @response {
     *     "current_page": 1,
     *     "data": [
     *         {
     *             "id": 1,
     *             "license_plate": "ABC123",
     *             "model": "Bus Model",
     *             "owner_name": "Owner Name",
     *             "bus_station_id": 1,
     *             "created_at": "2022-01-01 00:00:00",
     *             "updated_at": "2022-01-01 00:00:00"
     *         },
     *         ...
     *     ],
     *     "first_page_url": "...",
     *     "from": 1,
     *     "last_page": 1,
     *     "last_page_url": "...",
     *     "next_page_url": null,
     *     "path": "...",
     *     "per_page": 15,
     *     "prev_page_url": null,
     *     "to": 3,
     *     "total": 3
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(GetBusRequest $request)
    {
        $user = $request->user();
        //return $user->id;
        $buses = Bus::whereHas('BusStation', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->when($request->search && $request->search_by, function ($query) use ($request) {
                if ($request->search_by === 'license_plate') {
                    $query->where('license_plate', 'like', '%' . $request->search . '%');
                } elseif ($request->search_by === 'model') {
                    $query->where('model', 'like', '%' . $request->search . '%');
                } elseif ($request->search_by === 'owner_name') {
                    $query->where('owner_name', 'like', '%' . $request->search . '%');
                }
            })
            ->when($request->order_by, function ($query) use ($request) {
                $query->orderBy('created_at', $request->order_by);
            })
            ->paginate();

        return $buses;
        //return response('asasasasa');

        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBusRequest $request)
    {

        $data = $request->validated();

        try {
            $response = DB::transaction(function () use ($data) {
                $bus = Bus::create($data);
                $busstation = BusStation::where('user_id', request()->user()->id)->first();

                $bus->BusStation()->attach($busstation->id);

                return [
                    'bus' => $bus
                ];
            });
            return response($response);
        } catch (\Exception $ex) {
            return response()->json(['General Exeption = ', $ex->getMessage()], 500);
        } catch (\Error $er) {
            return response()->json(['General Error = ', $er->getMessage()], 500);
        } catch (QueryException $qr) {
            return response()->json(['General Exeption = ', $qr->getMessage()], 500);
        }
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function add_or_remove_driver_to_bus_here(AddDriverToTripRequest $request, Bus $bus)
    {
        //$data=$request->validated();
        $data = $request->validated();

        try {
            $response = DB::transaction(function () use ($data, $bus) {
                $bus->update($data);

                return [
                    'bus' => $bus,
                    'Message' => 'Driver Added'
                ];
            });
            return response($response);
        } catch (\Exception $ex) {
            return response()->json(['General Exeption = ', $ex->getMessage()], 500);
        } catch (\Error $er) {
            return response()->json(['General Error = ', $er->getMessage()], 500);
        } catch (QueryException $qr) {
            return response()->json(['General Exeption = ', $qr->getMessage()], 500);
        }

        //$bus->update($data);

        // return $bus;
        // return response('sdsdsdsdsd');
    }
}
