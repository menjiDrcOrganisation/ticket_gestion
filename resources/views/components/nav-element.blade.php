
@php
   $isActive = $active ?? false;
$classe = $isActive
            ? 'inline-flex items-center py-1 pl-2 bg-blue-200 text-white rounded-xl  border-indigo-400 text-sm font-medium leading-5  focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center py-1 pl-2   border-transparent text-sm font-medium leading-5  hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a href="{{$action}}" class="sidebar-link {{$classe}}  justify-between w-full gap-2  ">
    <div class="items-center" >
        <div class="h-10 w-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
        
        </div>
    </div>
    <span class="flex-1 text-base font-medium">{{$title}}</span>
</a>