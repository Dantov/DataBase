let	content = document.getElementById('content');
	content.classList.add('hidden');
let buttonBlock3D = document.getElementById('buttonBlock3D');
	buttonBlock3D.classList.toggle('hidden');
let buttClose = document.getElementById('buttClose');
	buttClose.classList.toggle('hidden');
let body = document.getElementById('body');
	body.classList.add('body');

let info3D = document.getElementById('info3D');
if ( _IS_DESKTOP_ )
	info3D.classList.toggle('hidden');

let dellStlForm = document.getElementById('dellStlForm');
let f_num = dellStlForm.children.length;
let names = [];
for ( let i = 0; i < f_num; i++ ) {

	names[i] = dellStlForm.children[i].getAttribute('value');
}

let camera, scene, renderer, control;

			init();
			
			function init() {
				let innW = window.innerWidth-5;
				let innH = window.innerHeight-5;
				
				renderer = new THREE.WebGLRenderer();
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( innW, innH );
				renderer.setClearColor(0xc8c7ff);
				document.body.appendChild( renderer.domElement );

				let target = new THREE.Vector3( 0, 20, 0 );
				
				camera = new THREE.PerspectiveCamera( 50, innW / innH, 1, 5000 );
				camera.position.set( 100, 50, 100 );
				camera.lookAt( target );
				
				control = new THREE.TransformControls( camera, renderer.domElement );
				/*
				let trackballControls = new THREE.TrackballControls( camera );
					trackballControls.rotateSpeed = 2;
					trackballControls.zoomSpeed = 1.5;
					trackballControls.panSpeed = 1.5;
					trackballControls.noZoom = false;
					trackballControls.noPan = false;
					trackballControls.staticMoving = true;
					trackballControls.dynamicDampingFactor = 0.5;
				*/

				let orbitControls = new THREE.OrbitControls( camera, renderer.domElement );
					orbitControls.target = target;
					orbitControls.zoomSpeed = 1.5;
					orbitControls.mouseButtons = {
						ORBIT: THREE.MOUSE.RIGHT,
						PAN: THREE.MOUSE.MIDDLE
					};
					orbitControls.saveState();
					orbitControls.update();

				scene = new THREE.Scene();
				let gridHelper = new THREE.GridHelper( 100, 10 );
				let axesHelper = new THREE.AxesHelper(100);
				scene.add( gridHelper );
				scene.add( axesHelper );
				
				let pointLight_Camera = new THREE.PointLight( 0xffffff, 0.95, 600 );
				let ambientLight = new THREE.AmbientLight(0xfff, 0.2);
				let light = new THREE.DirectionalLight( 0xffffff, 0.65 );
				let light2 = new THREE.DirectionalLight( 0xffffff, 0.65 );
					light.position.set( 100, 100, 100 );
					light2.position.set( -100, -100, -100 );
					
				camera.add( pointLight_Camera );
				
				scene.add( light );
				scene.add( light2 );
				scene.add( ambientLight );
				scene.add( camera );
				
				let pivots = [];
				let objects = [];
				let helpers = [];
				let boxes = [];
				let j = 0;
				let loader = new THREE.STLLoader();
				
				for ( let i = 0; i < f_num; i++ ) {
					
					loader.load( names[i], function ( geometry ) {
						
						let material = new THREE.MeshPhongMaterial();
							material.color.setHex( Math.random() * 0xffffff );
							
						objects[j] = new THREE.Mesh( geometry, material );
						objects[j].name = j;
						objects[j].scale.set(5,5,5);
						objects[j].rotation.set( setDegr(-90), 0, 0 );
							
						console.log( objects[j] );
						
						boxes[j] = new THREE.Box3().setFromObject( objects[j] );
						
						helpers[j] = new THREE.BoxHelper(objects[j], 0xffff00 );
						helpers[j].name = j;
						
						 // this re-sets the mesh position
						boxes[j].getCenter( objects[j].position );
						objects[j].position.multiplyScalar( - 1 ); // умножает на -1 (меняет знаки на против. в этом векторе)
						
						boxes[j].getCenter( helpers[j].position );
						helpers[j].position.multiplyScalar( - 1 );
						
						//Then add the mesh to a pivot object:
						pivots[j] = new THREE.Group();
						boxes[j].getCenter( pivots[j].position ); // записывает в этот вектор центр mesh
						
						
						pivots[j].add( objects[j] );
						
						scene.add( pivots[j] );
						scene.add( helpers[j] );
						helpers[j].visible = false;
						j++;
						
					});
				}
				
				window.addEventListener( 'resize', onWindowResize, false );
				window.addEventListener( 'keydown', function ( event ) {
					switch ( event.keyCode ) {
						case 81: // Q
							control.setSpace( control.space === "local" ? "world" : "local" );
							break;
						case 17: // Ctrl
							control.setTranslationSnap( 10 );
							control.setRotationSnap( THREE.Math.degToRad( 15 ) );
							break;
						case 87: // W
							control.setMode( "translate" );
							break;
						case 69: // E
							//control2.setMode( "rotate" );
							control.setMode( "rotate" );
							break;
						case 187:
						case 107: // +, =, num+
							control.setSize( control.size + 0.1 );
							break;
						case 189:
						case 109: // -, _, num-
							control.setSize( Math.max( control.size - 0.1, 0.1 ) );
							break;
					}
				});
				
				window.addEventListener( 'keyup', function ( event ) {
					switch ( event.keyCode ) {
						case 17: // Ctrl
							control.setTranslationSnap( null );
							control.setRotationSnap( null );
							break;
					}
				});
				
				renderer.domElement.addEventListener( 'dblclick', clearControl, false );
				renderer.domElement.addEventListener( 'click', onMouseClick, false );
				//window.addEventListener( 'mousemove', onMousemove, false );
				/*
				function onMousemove( event ) {
		
					event.preventDefault();
					
					mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
					mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
					console.log('x = ',mouse.x, 'y = ', mouse.y);
				}
				*/
				renderer.domElement.addEventListener( 'mouseup', function(event) {
					if ( event.which == 3 ) renderer.domElement.classList.toggle('cursorRotate');
				}, false );
				renderer.domElement.addEventListener( 'mousedown', function(event) {
					if ( event.which == 3 ) renderer.domElement.classList.toggle('cursorRotate');
				}, false );
				renderer.domElement.addEventListener( 'mouseup', function(event) {
					if ( event.which == 2 ) renderer.domElement.classList.toggle('cursorPUN');
				}, false );
				renderer.domElement.addEventListener( 'mousedown', function(event) {
					if ( event.which == 2 ) renderer.domElement.classList.toggle('cursorPUN');
				}, false );
				
				// raycaster try
				let raycaster = new THREE.Raycaster();
				let mouse = new THREE.Vector2(), INTERSECTED, SELECTED;

				let objSize = new THREE.Vector3();
				let camPosition = new THREE.Vector3();
				let selectionBOX = new THREE.Box3();

				function onMouseClick( event ) {
		
					//event.preventDefault();
					
					mouse.x = ( event.clientX / innW ) * 2 - 1;
					mouse.y = - ( event.clientY / innH ) * 2 + 1;
					
					// find intersections
					//console.log( helpers );
					raycaster.setFromCamera( mouse, camera );
					let intersects = raycaster.intersectObjects( objects );
					
					if ( intersects.length > 0 ) {
						//console.log('intersects = ', intersects );
						
						if ( SELECTED != intersects[0].object ) {
							
							if ( SELECTED ) {
							//	console.log( SELECTED.name );
								helpers[SELECTED.name].visible = false;
								//return;
							}
							
							SELECTED = intersects[0].object;
							
							helpers[SELECTED.name].visible = true;
							
							control.attach( pivots[SELECTED.name] );
							scene.add( control );

							/*selectionBOX.setFromObject(SELECTED);
							
							console.log( selectionBOX.min, selectionBOX.max );
							console.log( 'size = ', selectionBOX.getSize(objSize) );
							
							let helper = new THREE.Box3Helper( selectionBOX, 0xffff00 );
								helper.name = SELECTED.name;
							pivots[SELECTED.name].add( helper );
							//scene.add( helper );
							*/
							
							//SELECTED.material.color.setHex( Math.random() * 0xffffff );
							//SELECTED.currentHex = SELECTED.material.color.getHex();
							//console.log( SELECTED.name );
						}
						
					} else {
						//console.log( 'length = ', intersects.length );
						
						//if ( SELECTED ) SELECTED.material.emissive.setHex( SELECTED.currentHex );
						
						//SELECTED = null;
						//scene.remove( control );
					}
				}
				
				function clearControl( event ) {
		
					//event.preventDefault();
					
					mouse.x = ( event.clientX / innW ) * 2 - 1;
					mouse.y = - ( event.clientY / innH ) * 2 + 1;
					
					// find intersections
					raycaster.setFromCamera( mouse, camera );
					let intersects = raycaster.intersectObjects( objects );
					
					if ( intersects.length > 0 ) {
						
						if ( SELECTED != intersects[ 0 ].object ) SELECTED = intersects[ 0 ].object;
						
						let box = new THREE.Box3().setFromObject(SELECTED);
							box.getCenter( target );
						
						//console.log( 'Center of obj = ', target );
						//trackballControls.target = target;
						orbitControls.target = target;
						camera.lookAt( target );
						
						camera.getWorldPosition( camPosition ); //взяли позицию камеры в 3д
						let dist = box.distanceToPoint ( camPosition ); // расстояние до камеры
						
						//console.log( 'Distance to camera = ', dist );
						camera.translateZ( 40 - dist );
						
					} else {
						if (SELECTED) {
							helpers[SELECTED.name].visible = false;
							SELECTED = null;
							scene.remove( control );
						}
						
					}
					
				}
				
				function render() {
					requestAnimationFrame( render );
					
					pointLight_Camera.position = camera.position; // свет привязан к камере
					
					control.update();
					//trackballControls.update();
					orbitControls.update();
					if ( SELECTED ) {
						helpers[SELECTED.name].update();
					}
					//renderer.render(backgroundScene , backgroundCamera );
					renderer.render( scene, camera );
				}
				render();
				
				function onWindowResize() {
					
					let innW = window.innerWidth-5;
					let innH = window.innerHeight-5;
					
					camera.aspect = innW / innH;
					camera.updateProjectionMatrix();
					renderer.setSize( innW, innH );
					render();
				}
				
				// BUTTONS
			
				buttClose.onclick = function()
				{
					let formData = new FormData(dellStlForm);
					$.ajax({
						url: "/model-view/dellstl",
						type: "POST",
						data: formData,
						//dataType: "JSON", //// не работает с new FormData
                        processData: false,
                        contentType: false,
						success:function(resp) {
                            //resp = JSON.parse(resp);
                            //debug(resp);
							location.reload(true);
						},
						error:function (err) {
							debug(err,'3D view buttClose Error: ');
                        }
					})
				};

				let buttAnim = document.getElementById('anim');
					buttAnim.onclick = function(){
						
						if ( orbitControls.autoRotate ) {
							orbitControls.autoRotate = false;
						} else {
							orbitControls.autoRotate = true;
						}
					};

				let buttColor = document.getElementById('color');
					buttColor.onclick = function(){
						
						if ( SELECTED ) {
							SELECTED.material.color.setHex( Math.random() * 0xffffff );
						}
					};
				
				let buttGrid = document.getElementById('grid');
					buttGrid.onclick = function(){
						gridHelper.visible = !gridHelper.visible;
						axesHelper.visible = !axesHelper.visible;
					};
                buttGrid.click();
				
				let butt3D = document.getElementById('butt3D');
					butt3D.onclick = function(){
						
						buttonBlock3D.classList.toggle('hidden');
						content.classList.add('hidden');
						
						body.classList.add('body');
						body.lastElementChild.classList.remove('hidden');
						buttClose.classList.toggle('hidden');
						info3D.classList.toggle('hidden');
					};

			} // INIT
			
			
			
			
			function setDegr(degr) { // переводим градусы в радианы
				return degr*(3.14/180);
			}