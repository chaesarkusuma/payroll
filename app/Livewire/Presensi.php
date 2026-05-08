<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Carbon;

class Presensi extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;

    public function render()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        $insideRadius = $this->insideRadius;
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->whereDate('created_at', now())->first();

        // return dd($attendance);
        return view('livewire.presensi', compact('schedule', 'insideRadius', 'attendance'))->layout('layouts.main');
    }

    public function store()
    {
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        if ($schedule) {
            $attendance = Attendance::where('user_id', Auth::user()->id)
                ->whereDate('created_at', now())->first();

            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => Auth::user()->id,
                    'schedule_latitude' => $schedule->office->latitude,
                    'schedule_longitude' => $schedule->office->longitude,
                    'schedule_start_time' => $schedule->shift->start_time,
                    'schedule_end_time' => $schedule->shift->end_time,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'start_time' => Carbon::now()->toTimeString(),
                    'end_time' => Carbon::now()->toTimeString(),
                ]);

                Notification::make()
                    ->title('Presensi berhasil')
                    ->body('persensi berhasil.')
                    ->success()
                    ->send();
            } else {
                $attendance->update([
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
                Notification::make()
                    ->title('Presensi berhasil')
                    ->body('presensi berhasil diupdate.')
                    ->success()
                    ->send();
            }

            return redirect('/dashboard/attendances');
        }
    }
}