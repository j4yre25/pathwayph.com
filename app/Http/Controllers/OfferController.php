<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Messages;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required','integer','exists:job_applications,id'],
            'receiver_id'    => ['required','integer','exists:users,id'],
            'start_date'     => ['required','date','after_or_equal:today'],
            'message'        => ['required','string','max:2000'],
            'job_title'      => ['required','string','max:200'],
            'file'           => ['nullable','file','mimes:pdf','max:4096'],
        ]);

        $app = JobApplication::findOrFail($data['application_id']);
        if ($request->user()->cannot('update',$app)) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('offer_letters','public');
        }

        $msg = Messages::create([
            'application_id' => $app->id,
            'sender_id'      => $request->user()->id,
            'receiver_id'    => $data['receiver_id'],
            'message_type'   => Messages::TYPE_OFFER_LETTER,
            'content'        => $data['message'],
            'status'         => Messages::STATUS_UNREAD,
            'meta'           => [
                'job_title'  => $data['job_title'],
                'start_date' => $data['start_date'],
                'file'       => $path,
            ],
        ]);

        $offer = JobOffer::createFromMessage([
            'application_id' => $app->id,
            'message_id'     => $msg->id,
            'job_title'      => $data['job_title'],
            'body'           => $data['message'],
            'start_date'     => $data['start_date'],
            'file_path'      => $path,
        ]);

        return response()->json([
            'message' => 'Offer sent',
            'data'    => [
                'message' => $msg,
                'offer'   => $offer,
            ]
        ],201);
    }
}