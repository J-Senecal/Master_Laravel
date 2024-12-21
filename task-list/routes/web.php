<?php
use Illuminate\Http\Request;
use Illumnate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Models\Task;
use App\Http\Requests\TaskRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function() {
    return redirect()->route('tasks.index');
});

// Route::get('/tasks', function () use ($tasks) { //use statement allows access to variables outside of anonymous functions 
//     return view('index', [ //pass data ( variable) to view in key:value pairs
//         'tasks' => $tasks
//     ]);
// })->name('tasks.index');

Route::get('/tasks', function () {  
    return view('index', [ 
        'tasks' => Task::latest()->paginate(10)  //paginate(#of entires per page) will separate data into pages for you rather than display all on same page
        //'tasks' => \App\Models\Task::latest()->get() //latest() is a query method that allows you to create SQL queries and then get() method executes that query to retrieve data
    ]);
})->name('tasks.index');

Route::view('/tasks/create' , 'create')
    ->name('tasks.create');

Route::get('/tasks/{task}/edit' ,function(Task $task) {
    return view('edit', [
        'task' => $task
    ]);
})->name('tasks.edit');

Route::get('/tasks/{task}' ,function( Task $task) {
    return view('show', ['task' => $task
    ]);
})->name('tasks.show');

Route::post('/tasks', function(TaskRequest $request) { //request object gives us access to data being submitted
    
    // $data = $request->validate([ //create validations to require information and format of transmitting
    //     'title' => 'required|max:255',
    //     'description'=>'required',
    //     'long_description'=>'required'
    // ]);
    // $data->$request->validated();
    // $task= new Task; //set a variable to a new instance of the model
    // $task->title = $data['title'];  //use form data to add to instance
    // $task->description = $data['description'];
    // $task->long_description = $data['long_description'];
    // $task->save();

    $task= Task::create($request->validated()); // this takes the place of lines 57-62 creating the array of data and automatically adds it to the model instance

    return redirect()->route('tasks.show', ['task'=>$task->id])
        ->with('success', 'Task created successfully!'); //adding flash messages
})->name('tasks.store');

Route::put('/tasks/{task}', function (Task $task, TaskRequest $request) { 

    // $data=$request->validated();
    // $task->title = $data['title'];  
    // $task->description = $data['description'];
    // $task->long_description = $data['long_description'];
    // $task->save();

    $task-> update($request->validated());

    return redirect()->route('tasks.show', ['task'=>$task->id])
        ->with('success', 'Task updated successfully!'); //adding flash messages
})->name('tasks.update');

Route::delete('/tasks/{task}', function (Task $task) {
    $task->delete();

    return redirect()->route('tasks.index')
        ->with('success', 'Task deleted successfully!');
})->name('tasks.destroy');

Route::put('tasks/{task}/toggle-complete', function (Task $task) {
    $task->toggleComplete();
    // $task->completed = !$task->completed;  these lines get replaced by method inside of task model toggleComplete()
    // $task->save();

    return redirect()->back()->with('success', 'Task updated successfully!');
})->name('tasks.toggle-complete');

// Route::get('/hello', function () {
//     return 'hello world';
// })-> name('hello');

// Route::get('/hallo' , function() {
//     return redirect() ->route('hello');
//});
//name a route so that way if url changes, the application will still direct to correct page

Route::get('/greet/{name}', function ($name) {
    return 'hello ' . $name . '!';
});

Route::fallback( function() {
    return  'still got somewhere!';
});
//return something for all routes that don't exist