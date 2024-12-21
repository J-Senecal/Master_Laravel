@extends('layouts.app')

@section('title' , isset($task) ? 'Edit Task':'Add Task') <!-- if task is set, display edit title, if not then it's a new task -->

{{--@section('styles')  make sure to add this to layout 
<style>
    .error-message {
        color:red;
        font-size: 0.8rem;
    }
</style>
@endsection this entire block replaced by class styling in app.blade.php--}}



@section('content')
    <form action="{{ isset($task) ? route('tasks.update', ['task' => $task->id]) : route('tasks.store')}}" method="post"> <!-- if task is set, then go to update route, if not then create new -->
        @csrf
        @isset($task) <!-- if task is set, then need to spoof method so it will update with PUT -->
            @method('PUT')
        @endisset
        <div class="mb-4">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" 
                @class(['border-red-500' => $errors->has('title')])
                value="{{ $task->title ?? old('title')}}"> <!-- ?? operator will check if task title is set and use it, otherwise if not it will cause no error and leave blank -->
            
            @error('title')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5"
            @class(['border-red-500' => $errors->has('description')])>{{ $task->description ?? old('description')}}</textarea>
            @error('description')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="long_description">Long Description</label>
            <textarea name="long_description" id="long_description" rows="10" 
                @class(['border-red-500' => $errors->has('long_description')])>{{ $task->long_description ?? old('long_description')}}
            </textarea>
            @error('long_description')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn">
                @isset($task)
                    Update Task
                @else
                    Add Task
                @endisset
            </button>
            <a href="{{ route('tasks.index') }}" class="link">Cancel</a>
        </div>
    </form>
@endsection
