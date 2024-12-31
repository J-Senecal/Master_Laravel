<?php

/*
Laravel App Structure
-app (logic of the application)
--http/Controllers
--Models
--Providers
-bootstrap(routes and middleware)
-config (defines basic laravel functions)
-database
-public
--.htaccess - apache server file to direct all traffic to index.php
--index.php
-resources
--JS
--CSS
--views (Blades)
---layouts (displays same info on every blade to reduce redundant coding)
-routes
-storage
-tests
-vendor


workflow:
create project command
create controllers
create models and migrations
edit migrations with columns needed in db
add relationships inside models 
seed the db

*/

//----------methods --------//
collect() //converts arrays to Laravel collection objects so you can call methods and do operations on the array
create() //creates an instance of a model and saves it to db
endisset()- //closes the block around which statements the isset function is checking
explode() // turns string data into an array
in_array('method', data) // search in array and will apply method (like trim) to the data.  trim removes blank spaces
isset() - //function to determine whether a variable is set
load() //only works with models that have been already loaded
old() //keeps form data if submitted correctly so even if other inputs have errors you don't have to retype
with() //works to get the model and data at same time




//--------------------------------------------------------------------APIs------------------------------------------------------------------------//
//create the routes/api.php file   //answer yes to db migrations question
php artisan install:api 
add the [Laravel\Sanctum\HasApiTokens] trait to your User model.




//-------API routing --------//
api.php not web.php

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->get('/user', function (Request $request) {
        return $request->user();
    });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Public routes
Route::apiResource('events', EventController::class) //apiResources only registers routes to CRUD single resources, not routes for the forms
    ->only(['index', 'show']);

// Protected routes
Route::apiResource('events', EventController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'throttle:api']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('events.attendees', AttendeeController::class)
        ->scoped()
        ->only(['store', 'destroy']);
});

Route::apiResource('events.attendees', AttendeeController::class)
    ->scoped()
    ->only(['index', 'show']);





//---------------------------------------------------------------Authentication------------------------------------------------------------------//


//Laravel provides two primary ways of authorizing actions: gates and policies. Think of gates and policies like routes and controllers. Gates provide a simple, closure-based approach to authorization while policies, like controllers, group logic around a particular model or resource.  Everyone can create an event (gate) but only certain authenticated users can create/modify an event ( policies)
//in AppServiceProvider


//gates
//Typically, gates are defined within the boot method of the App\Providers\AppServiceProvider class using the Gate facade. Gates always receive a user instance as their first argument and may optionally receive additional arguments such as a relevant Eloquent model.

use Illuminate\Support\Facades\Gate;

use App\Models\Post; //models used in function
use App\Models\User; //models used in function
use Illuminate\Support\Facades\Gate;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Gate::define('update-event', function (User $user, Post $post) { //define the gate ('gateName'), function(closure for action to be performed for model arguments)
        return $user->id === $post->user_id; // if $user id matches the post creators user id, then they can perform the specific operation 
    });
}




//policies
//Policies are classes that organize authorization logic around a particular model or resource. For example, if your application is a blog, you may have an App\Models\Post model and a corresponding App\Policies\PostPolicy to authorize user actions such as creating or updating posts.

php artisan make:policy PolicyName --model=ModelName  //typical naming convention for policy is the modelname and then policy ie PostPolicy for this example


use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
 
/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Gate::define('update-post', [PostPolicy::class, 'update']);
}






//------------------------------------------------Blade Templates---------------------------------------------//

//Blades are stored in the resources/views folder and have .blade.php extension.  It is assumed within the view() so just the name of the file is required, no extensions

Route::get('/tasks', function () use ($tasks) { //use statement allows access to variables outside of anonymous functions 
    return view('index', [ //pass data ( variable) to view in key:value pairs. Key is the variable, value is the data that will be passed
        'tasks' => $tasks
    ]);
})->name('tasks.index');

//-----blade subviews----//

//these are reusable views for things that have the same style (ex. create and update form blades)


//Layouts

//Inherit code from one layout blade to be applied to all blades so you don't have to code the same information on every blade template. 

@extends('layouts.app') // folderName.bladeLayoutName

// inside the layout need to use
@yield('title') //name whatever you want that content to be, then inside the blade template that extends the layout, define the section using @section('title')


//------------------------------------------------Caching-------------------------------------------------------------//

RootFolder\config\cache.php

//option 1 - use a facade and call the model
//in controller
use Illuminate\Support\Facades\Cache;
//inside function in controller
$cacheKey = 'books:' . $filter . ':' . $title;
$varName = Cache::remember($cacheKey, 3600, fn() => $books->get()); // arguments are the 'key name' or variable, time to be stored (in seconds) and a function to return the data

//option 2 - use a function
$varName = cache()->remember('keyName', 3600, fn() => $books->get()); //key can also be a variable as above

//clear cache (in controller)

protected static function booted() //clear the cache when review is updated to it pulls new updated info from db
    {
        static::updated(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::deleted(fn(Review $review) => cache()->forget('book:' . $review->book_id));
    }


//------------------------------------------------Component-----------------------------------------------------------//

php artisan make:component ComponantName // will make php file in app/view/components/ComponantName.php as well as a blade file in resources\views\components

// define the properties of the component class in the constructor function and all public properties will be passed to component view
//don't have to import or register them, they just work in all templates automatically due to Laravel 

//html tag 
<x-bladeFileName />






//------------------------------------------------Controllers---------------------------------------------------------//

php artisan make:controller ControllerName --resource



load() //only works with models that have been already loaded
    public function show(Event $event)
    {
        $event->load('user', 'attendees'); //running the show route will load users and attendees to that event
        return new EventResource($event);
    }

with() //works to get the model and data at same time
    return EventResource::collection(Event::with('user')->get());

all() //gets all the data 
    return Event::all(); 



//------scoping resource routes------//

use App\Http\Controllers\ControllerName;

Route::resource('books.reviews', ControllerName::class)
    ->scoped(['review' => 'book'])
    ->only(['create', 'store']);





//----------------------------------------------- command prompts ----------------------------------------------------//




composer create-project --prefer-dist laravel/laravel ProjectName //create new project folder with required packages
php artisan install:api //create the routes/api.php file
php artisan make:request RequestNameHere

php artisan make:controller ControllerName --resource //create controller file in resource folder
php artisan route:list  //will list all routes in app
php artisan tinker //can write SQL queries on command line and get database data directly in terminal
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" // publish copies files and put them into your project folder.  Vendor files should never be changed as they can be overwritten when doing updates and such. 

//--------custom artisan commands----------//

//anatomy of a command
php artisan {{signature}} {{description}} // signaute in example below is make:command and description is NameOfCommand

php artisan make:command NameOfCommand
//found in
app/console/commands 



namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = \App\Models\Event::with('attendees.user') //set variable to event class with attendees (only want events with attendees)
            ->whereBetween('start_time', [now(), now()->addDay()]) //events that have start time between now and 24 hours
            ->get();// get all the events that fit into these parameters

        $eventCount = $events->count(); //count the total number of events within params
        $eventLabel = Str::plural('event', $eventCount); // change the label to plural if there is more than one event

        $this->info("Found {$eventCount} {$eventLabel}."); //output on the command line "found # event(s)"

        $events->each( //built in method to run closure functions (iterate) on every event in this collection
            fn($event) => $event->attendees->each( //each event has attendees which are also a collection to you can run each() again
                fn($attendee) => $attendee->user->notify( //this will notify each attendee in the event 
                    new EventReminderNotification(
                        $event
                    )
                )
            )
        );

        $this->info('Reminder notifications sent successfully!');
    }
}





//----------------------------------------------------Database-------------------------------------------------------//
/*
.env file has database config
config>database.php pulls info from .env file 

command
php artisan migrate


lazy load - defers initialization of an object until the object is needed (less data, smaller db)
eager load - initializes an object upon creation (better for large amounts of records)
*/




//------------------------Docker---------------------------------//

//move docker-compose.yml into root folder.  You can adjust port and password in this folder
//command prompt-   docker compose up
/*
url - localhost:8080 
system- MySQL
server - mysql
username- root
password - root ( or whatever is specified in docker-compose.yml file)
database - blank
*/



//--------------------------------------------------factories----------------------------------------------------------//

php artisan make:factory factoryName --model=modelName
//provide a way to populate database tables with fake data for testing
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $fillable = ['review', 'rating']; //fillable allows values assigned inside the array to be mass assigned

    public function definition(): array
    {
        return [  // the keys are the names of the columns in the db and the values are the values in those columns
            'name' => fake()->name(), //fake() utilizes the PHP library Faker
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}


//----------------------------------------------------------Login/Registration----------------------------------------------------------//

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)  //pass in request object, form data from user login
    {
        $request->validate([ //validate information required in the form
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = \App\Models\User::where('email', $request->email)->first(); //find the email in db that matches the email field in the request

        if (!$user) {
            throw ValidationException::withMessages([ //if no user is found with that email address, then throw error
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        if (!Hash::check($request->password, $user->password)) { //if password doesn't match the users' pw in the db, then throw error. check(input data, existing data)
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken; // createToken() is a trait in the User model

        return response()->json([
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); 

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

// Revoke all tokens...
$user->tokens()->delete();

// Revoke the token that was used to authenticate the current request...
$request->user()->currentAccessToken()->delete();

// Revoke a specific token...
$user->tokens()->where('id', $tokenId)->delete();


//----------------------------------------------------------------Models----------------------------------------------------------------//

//model names are singular, migrations create a table name that is the plural of the model name\
create() // create new model instance
update() // update existing model instance

php artisan make:model modelNameHere -m //-m will also create a migration file


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; //need to add this in order to use Factory to seed Db

class Task extends Model
{
    use HasFactory; // add this use statement to have factories seed Db
}



//-----------------------------------------------------Middleware----------------------------------------------------------------//

//inside App/Http/middleware folder
//Middleware is registered inside the route definition files

Route::get('/profile', [UserController::class, 'show'])->middleware('auth');

//inside controller

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('log', only: ['index']),
            new Middleware('subscribed', except: ['store']),
        ];
    }

    // ...
}



//-----------------------------------------------------Migrations----------------------------------------------------------------//

//Migrations are like version control, with up() forward or down() which rolls back changes
php artisan migrate //will add changes of your model to your database
php artisan migrate:rollback //this will rollback to the last migration
php artisan make:migration create_tableName_table //use this command to create a migration to add a new table to the Db. 
php artisan db:seed //adds to database
php artisan migrate:refresh --seed //erases all data in db and seeds with current data in migration


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) { //first argument is table name, second is function to create object
            $table->id(); // these are columns in the table, add as many as you want
            
            $table->foreignIdFor(User::class); //foreignIdFor() creates a foreign key column in the specified model class table (User::class)
            $table->foreignIdFor(Event::class);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};


//--------------------------------------------------Models-----------------------------------------------------------------------//

//depending on what you have in your migration up() will determine what relationships you need to build into the model

//traits
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory; //this is a trait.  It will extend to all instances of this class

    protected $fillable = ['name', 'description', 'start_time', 'end_time', 'user_id'];  //fillable means that you can update multiple fields in bulk at same time
    protected $guarded = ['fields that cannot be mass assigned']; //this method is prone to errors

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); //one user belongs to one event
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class); //one event has many attendees
    }
}



//------------------------------------------------------Notifications------------------------------------------------------//

php artisan make:notification NameOfNotification
// found in folder app/notifications

//Notifications may be sent in two ways: using the notify method of the Notifiable trait or using the Notification facade. The Notifiable trait is included on your application's App\Models\User model by default:

//Add Notifiable trait in the user model (usually in the user model)
Namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class User extends Authenticatable
{
    use Notifiable;
}
//The notify method that is provided by this trait expects to receive a notification instance:

use App\Notifications\InvoicePaid;
 
$user->notify(new InvoicePaid($invoice));



//------------------------------------------------------Postman------------------------------------------------------//

// header & : key=accept , value=application/json  --this will show you errors in json so you know what was wrong with the request
//body(raw JSON): key=accept , value=application/json  --this is how you post data through the api.  Remember double quotes "key" : "value",
//authorization - set to Bearer to add Sanctum Token
    // to have token automatically set add JS script to Tests tab
    const json = JSON.parse(responseBody)
    pm.environment.set("TOKEN", json.token); //creates a new environment with token saves
    //then add the token as a variable in the authorization Bearer field {{TOKEN}}



//----------------------------------------------------Query Builder--------------------------------------------------//

// for api the url will end with ? + the query, for this example it will be include
https:\\URL_Name?include=query1, query2, query3 
//if multiple options for queries, you can put them in a trait to be called on in the controller
trait CanLoadRelationships
{
    public function loadRelationships(
    Model|QueryBuilder|EloquentBuilder|HasMany $for, //loading the relationships for different model classes
    ?array $relations = null
    ): Model|QueryBuilder|EloquentBuilder|HasMany {
    $relations = $relations ?? $this->relations ?? []; //use relations if parameter is passed, otherwise use field defined inside the class the trait is used, otherwise it will be an empty array

    foreach ($relations as $relation) { //looping through the $relations array to load the values as a query builder
        $for->when(
        $this->shouldIncludeRelation($relation),
        fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation) //instanceof is built it method to check the class type 
        );
    }

    return $for;
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
    $include = request()->query('include');

    if (!$include) {
        return false;
    }

    $relations = array_map('trim', explode(',', $include));

    return in_array($relation, $relations);
    }
}

//----------local query scopes--------//

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder; //can't import more than one with same name so set it to new name (alias)

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class); //defines one to many relationship with Review class
    }

    public function scopeTitle(Builder $query, string $title): Builder //local query scope so when using tinker you can run method rather than specify all arguments in the query. Uses the built in Builder model
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to) //calling private function daterangeFilter 
        ]);
    }

    public function scopeWithAvgRating(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvg([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to) 
        ], 'rating');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc');
    }
    
    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder
    {
        return $query->having('reviews_count', '>=', $minReviews); //when using aggregate methods you need to use having not where
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder|QueryBuilder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder|QueryBuilder
    {
        return $query->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }

    protected static function booted()
    {
        static::updated(
            fn(Book $book) => cache()->forget('book:' . $book->id)
        );
        static::deleted(
            fn(Book $book) => cache()->forget('book:' . $book->id)
        );
    }
}

//------------------------------------------------------Queues---------------------------------------------------------------------------//
/*
.env file change QUE_CONNECTION (default is sync which will run everything at same time bypassing the queue)
queue.php file has details about the connections (drivers)
*/
php artisan queue:work // process to pick up jobs to run and execute what's in the queue. THis que worker command needs to be restarted every time new code is added


//-----------------------------------------------Query Parameter(API)--------------------------------------------------------------------//


// ?include= after the API url is the query parameter - caling different data/parameters in the api call to return specific data
// https://URL_NAME_HERE?include=query_param1,query_param2,query_param3...

//EventController - all of this can be put into a trait to be used by all controllers, not just a single one

    public function index()
    {
        $query = Event::query();
        $relations = ['user', 'attendees', 'attendees.user']; // loading which relations can be added to API
        foreach ($relations as $relation) { //loop through the relations list
            $query->when(   //when method is called so that if found to be true it will run additional method
                $this->shouldIncludeRelation($relation), //if this relation matches what's in the include statement of the API it will run next line of code
                fn($q) => $q->with($relation)
            );
        }
        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');
        if (!$include) {
            return false;
        }
        $relations = array_map('trim', explode(',', $include));
        return in_array($relation, $relations);
    }


//---------------------------------------------------Rate Limiting-----------------------------------------------------------------------//


use Illuminate\Support\Facades\RateLimiter;

$executed = RateLimiter::attempt(
    'send-message:'.$user->id,  //key should be unique to action performd
    $perMinute = 5,  // specify a time limit
    function() {
        // action to be performed that needs to be rate limited
    }
);

if (! $executed) {
    return 'Too many messages sent!';
}

//APR rate limiting - in AppServiceProvider.php

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

}




//---------------------------------------------------------Requests---------------------------------------------------------------//
//good for validations as long as same exact validations for multiple routes

php artisan make:request RequestNameHere // found in App\Http\Requests



//---------------------------------------------------------Resources---------------------------------------------------------------//
https://laravel.com/docs/11.x/eloquent-resources

//Eloquent's resource classes allow you to expressively and easily transform your models and model collections into JSON.
php artisan make:resource ResourceName

app/Http/Resources //folder
use Illuminate\Http\Resources\Json\JsonResource

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'user' => new UserResource($this->whenLoaded('user')),
            'attendees' => AttendeeResource::collection(
                $this->whenLoaded('attendees')
            )
        ];
    }
}


//-----------------------------------------------------------Routes---------------------------------------------------------------//
//order of routes matter.  Routes with common paths might get caught in wrong route and fail. 

php artisan route:list //display all the route names defined in the app

Route::get('/tasks', function () use ($tasks) { //use statement allows access to variables outside of anonymous functions 
    return view('index', [ //pass data ( variable) to view in key:value pairs
        'tasks' => $tasks
    ]);
})->name('tasks.index');

Route::get('/tasks/{id}' ,function($id) {
    return view('show', [
        'task' => \App\Models\Task::findOrFail($id)]); //pass the model directly into the route.  
})->name('tasks.show');

findOrFail(); //if info is not found for specified route, it will abort and return a 404 error page
find(); //find the specified information for the route to return a view



//----------- scoped resource routing--------------//

use App\Http\Controllers\ControllerName;

Route::resource('books.reviews', ControllerName::class)
    ->scoped(['review' => 'book'])
    ->only(['create', 'store']);

//----------- route model binding --------------//

Route::get('/tasks/{id}' ,function($id) {
    return view('show', [
        'task' => \App\Models\Task::findOrFail($id)]); //pass the model directly into the route.  
})->name('tasks.show');

//becomes

Route::get('/tasks/{task}' ,function(Task $task) { // put model name and variable
    return view('show', [
        'task' => $task
    ]);
})->name('tasks.show');



//--------------------------------------------------------------Sanctum--------------------------------------------------------------------//

//comes preloaded with Laravel 11+ but check composer.json for 'required' apps
//if not run:
php artisan install:api
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" //copes vendor folder to your app, never modify vendor files as they will be overwritten
php artisan migrate // run migrations to create tokens table in db


use Laravel\Sanctum\HasApiTokens




//--------------------------------------------------------------seeders--------------------------------------------------------------------//

php artisan make:seeder SeederName
php artisan db:seed //will add data to db
php artisan migrate:refresh --seed //will clear the migrations so db will only have the current data you are seeding

//load something into the database
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(); //factory(# of models you want to generate)

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

//--------------------------------------------------------Task Scheduling----------------------------------------------------------------------//

//define all of your scheduled tasks in your application's routes/console.php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
 
Schedule::call(function () {
    DB::table('recent_users')->delete();
})->daily();


//If you prefer to reserve your routes/console.php file for command definitions only, you may use the withSchedule method in your application's bootstrap/app.php file to define your scheduled tasks. This method accepts a closure that receives an instance of the scheduler:

use Illuminate\Console\Scheduling\Schedule;
 
->withSchedule(function (Schedule $schedule) {
    $schedule->call(new DeleteRecentUsers)->daily();
})

//------------------------------------------------------------Tinker--------------------------------------------------------------------------//


php artisan tinker // opens up a shell command line that allows you to query the db directly and get results displayed within the shell
//if that doesn't work
php artisan serve>open up web.php in routes folder>main '/' get route return dd();

$varName = \App\Models\ModelName::query
//if you add toSQL() at the end of your query, you will see the actual SQL query being sent to the db 

//create
$review = $book->reviews()->create(['review' => 'Sample Review' , 'rating' => 5])  //crates a reivew of the book model and able to pass in values that are part of the constructor of the factory as well as defined in the fillable method of the model (because we are assigning more than one at once (mass assignment))

//read
$book = \App\Models\Book::with('reviews')->find(1); //find book with id 1
\App\Models\Book::where('title', 'LIKE', '%value%')->get(); //this is find all books where the title is whatever is in between the % (value)

//update
$review = \App\Models\Review::find(1); //loads a review with id of 1 and sets it to variable name review
$book2 = \App\Models\Book::find(2); //loads a  book with id of 2 and sets it to variable name book2
$book2->reviews()->save($review); //runs review function on book2 and saves the review data of $review to $book2



//------------------------------------------------------------Traits--------------------------------------------------------------------------//

//always added to classes by the use statement

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder; //can't have two different classes with the same name so use an alias - as AliasName
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait CanLoadRelationships
{
    public function loadRelationships(
    Model|QueryBuilder|EloquentBuilder|HasMany $for, //loading the relationships for different model classes based on whats in the model. Import them!
    ?array $relations = null
    ): Model|QueryBuilder|EloquentBuilder|HasMany {
    $relations = $relations ?? $this->relations ?? [];

    foreach ($relations as $relation) {
        $for->when(
        $this->shouldIncludeRelation($relation),
        fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation) //instanceof is built in to check what class type of a specific object.  If $q is a model you can load, otherwise if it's a querybuilder you need to use with()
        );
    }

    return $for;
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
    $include = request()->query('include');

    if (!$include) {
        return false;
    }

    $relations = array_map('trim', explode(',', $include));

    return in_array($relation, $relations);
    }
}






@if (count($record) === 1)
    I have one record!
@elseif (count($records) > 1)
    I have multiple records!
@else
    I don't have any records!
@endif


Loops
@for ($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor

@foreach ($users as $user)
    <p>This is user {{ $user->id }}</p>
@endforeach

Forelse loops through and also has an else statement if none are found
@forelse ($users as $user) 
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse


@while (true)
    <p>I'm looping forever.</p>
@endwhile
?>