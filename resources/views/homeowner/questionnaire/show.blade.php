@extends('layouts.homeowner')
@section('title', 'My Inspections')

@section('content')

    <div class="container my-4">
        <h1 class="h4 mb-4">Home Questionnaire</h1>

        <form action="{{ route('homeowner.questionnaire.update',1) }}" method="POST">
            @csrf

            @foreach ($questionnaire->responses ?? [] as $question => $answer)
                <div class="mb-3">
                    <label for="{{ $question }}" class="form-label">{{ ucwords(str_replace('_', ' ', $question)) }}</label>
                    <input type="text" name="responses[{{ $question }}]" id="{{ $question }}"
                        value="{{ old('responses.' . $question, $answer) }}" class="form-control">
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Save Questionnaire</button>
        </form>
    </div>
@endsection
