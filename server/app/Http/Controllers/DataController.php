<?php

namespace App\Http\Controllers;

use App\Models\User;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class DataController extends Controller
{
    public function index()
    {

        $users = User::all();

        $userData = [];

        /* $dummyData = [
            ['userid' => 1, 'username' => 'Itasdem 1', 'email' => 'Description 1'],
            ['userid' => 2, 'username' => 'Item 2', 'email' => 'Description 2'],
            ['userid' => 3, 'username' => 'Item 3', 'email' => 'Description 3'],
            // Add more dummy data as needed
        ]; */

        foreach ($users as $user) {
            $userData[] = [
                'userid' => $user->userid,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        return response()->json($userData);
    }

    public function upload(Request $request)
    {
        // Validate and process the uploaded file
        $uploadedFile = $request->file('file');

        if (User::exists()) {
            User::truncate(); // This will delete all data in the table
        }

        try {
            // Your file processing code here
            if (/* $uploadedFile || */true) {
                // Get the path to the uploaded file
                $filePath = $uploadedFile->getPathname();

                // Create a CSV Reader
                $csv = Reader::createFromPath($filePath, 'r');
                $csv->setHeaderOffset(0); // Assuming the first row is the header

                // Get the CSV data as an associative array
                $csvData = iterator_to_array($csv->getRecords());

                $newCsvData = [];
                foreach ($csvData as $key => $record) {
                    $newCsvData[$key - 1] = $record;
                }

                foreach ($csvData as $record) {
                    // Prepare an array for each user record
                    $userRecords[] = [
                        'userid' => $record['userid'],
                        'name' => $record['username'],
                        'email' => $record['email'],
                        'password' => 'test123',
                        'email_verified_at' => now(),
                    ];
                }

                // Use the insert method to insert all records in a single query
                User::insert($userRecords);

                // Return the processed data as a JSON response
                return response()->json($newCsvData);
            } else {
                return response()->json(['error' => 'No file uploaded'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function exportCsv()
    {
        // Fetch user data from the database
        $users = User::all();

        // Create a CSV Writer instance
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Insert the header row
        $csv->insertOne(['UserID', 'Name', 'Email']);

        // Insert user data
        foreach ($users as $user) {
            $csv->insertOne([$user->userid, $user->name, $user->email]);
        }

        // Set the headers for the response
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users.csv',
        ];

        // Return the CSV file as a download response
        return response()->stream(
            function () use ($csv) {
                echo $csv->getContent();
            },
            200,
            $headers
        );
    }
}
