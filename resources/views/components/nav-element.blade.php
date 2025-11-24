
@php
$classe = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5  focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border rounded-xl border-transparent text-sm font-medium leading-5  hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a href="{{$action}}" class="sidebar-link {{$classe}}  justify-between w-full   ">
    <div class="flex ">
        <div class="h-10 w-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-plus text-purple-600 text-lg"></i>
        </div>
        <span class="flex-1 text-base font-medium">{{$title}}</span>
    </div>
</a>