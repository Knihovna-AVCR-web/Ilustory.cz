@extends('layouts.front')
@section('content')
<style>
    header {
      position: absolute;
      width: 100%;
      z-index: 2;
    }
    .relative.hero {
      width: 100%;
      position: relative;
      background: black; 
      background-size: cover;
      overflow-x: hidden !important;
      align-items: center;
      text-align: center;
      justify-content: center;
    }
    .intro-heading {
      font-size: 50px;
      margin-top: 70px;
      width: 100%;
      position: relative;
    }
    h1 {
      
      margin-left: auto;
      margin-right: auto;
      font-weight: bold;
      color: #bf6d65 !important;
    }
    .front-page-image{
      width: 40%;
      display: block;
      margin-top: 3%;
      margin-left: auto;
      margin-right: auto;
    }
    @media only screen and (max-width: 600px){
      h1{
        font-size: 3vw;
      }
    }
    #ilustration{
        cursor: zoom-in;
    }
</style>

<div class="relative mb-12 hero">
    <div class="intro-heading">
        <h1>Literární soutěž Ilustory 2025</h1>
    </div>
    <div class="front-page-image">
        <a href="https://ilustory.cz/2025/wp-content/themes/ilustory-2025/resources/images/intro-2025/ilustrace-text-web.png" target="_blank" id="ilustration">
        <img src="https://ilustory.cz/2025/wp-content/themes/ilustory-2025/resources/images/intro-2025/ilustrace-text-web.png">
        </a>
    </div>
</div>

<div class="w-full px-6 mx-auto max-w-7xl">
    <article class="mx-auto prose-sm sm:prose">
        {{ the_content() }}
    </article>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.3.2/gsap.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var scene = document.getElementById('scene');
        var parallaxInstance = new Parallax(scene, {
            relativeInput: false,
            limitY: true,
            frictionY: 0
        });

        var container = document.querySelector("#magic");
        var count = 32;
        var w = 256;
        var h = 256;

        function random(min, max) {
            return gsap.utils.random(min, max);
        }

        function createUnit() {
            var unit = document.createElement("div");
            unit.classList.add("unit");
            container.appendChild(unit);
            gsap.set(unit, {
                x: random(0, w),
                y: random(0, h),
                scale: random(0.2, 0.6),
                opacity: 0
            });
            gsap.to(unit, {
                x: "+=" + random(-w / 2, w / 2),
                y: "+=" + random(-h / 2, h / 2),
                duration: random(20, 23),
                ease: Linear.easeNone
            });
            gsap.to(unit, {
                opacity: 1,
                repeat: 5,
                yoyo: true,
                duration: random(2, 3),
                delay: random(0, 3),
                ease: Power2.easeInOut,
                onComplete: function() {
                    unit.parentNode.removeChild(unit);
                    createUnit();
                }
            });
        }

        for (var index = 0; index < count; index++) {
            createUnit();
        }
    });
</script>
@endsection
