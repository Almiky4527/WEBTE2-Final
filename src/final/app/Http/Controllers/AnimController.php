<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\OctaveController;
use App\Enums\AnimType;

class AnimController extends Controller
{
    private function run($code)
    {
        $payload = [
            'token' => $this->get_token(),
            'code' => $code,
        ];

        $post_request = new Request($payload);

        $octave_controller = app()->make(OctaveController::class);

        return $octave_controller->evaluate($post_request);
    }

    /**
     * Run calculations for ball animation
     */
    public function run_ball(Request $request)
    {
        $code = "
            H = -m*g/(J/(R^2)+m);
            A = [0 1 0 0; 0 0 H 0; 0 0 0 1; 0 0 0 0];
            B = [0;0;0;1];
            C = [1 0 0 0];
            D = [0];   
            K = place(A,B,[-2+2i,-2-2i,-20,-80]);
            N = -inv(C*inv(A-B*K)*B);

            sys = ss(A-B*K,B,C,D);

            t = 0:0.01:5;
            r =0.25;
            initRychlost=0;
            initZrychlenie=0;
            [y,t,x]=lsim(
                N*sys,
                r*ones( size(t) ),
                t,
                [ initRychlost;0;initZrychlenie;0 ]
            );

            disp([y,t,x]);
        ";

        $params = $request->validate([
            'm' => 'required|numeric',
            'R' => 'required|numeric',
            'g' => 'required|numeric',
            'J' => 'required|numeric',
        ]);

        $code = "m = " . $params['m'] . ";" .
                "R = " . $params['R'] . ";" .
                "g = " . $params['g'] . ";" .
                "J = " . $params['J'] . ";" .
                $code;

        return $this->run($code);
    }

    /**
     * Run calculations for pendulum animation
     */
    public function run_pendulum(Request $request)
    {
        $code = "
            p = I*(M+m)+M*m*l^2;
            A = [0 1 0 0; 0 -(I+m*l^2)*b/p (m^2*g*l^2)/p 0; 0 0 0 1; 0 -(m*l*b)/p m*g*l*(M+m)/p 0];
            B = [ 0; (I+m*l^2)/p; 0; m*l/p];
            C = [1 0 0 0; 0 0 1 0];
            D = [0; 0];
            K = lqr(A,B,C'*C,1);
            Ac = [(A-B*K)];
            N = -inv(C(1,:)*inv(A-B*K)*B);

            sys = ss(Ac,B*N,C,D);

            t = 0:0.05:10;
            r =0.2;
            initPozicia=0;
            initUhol=0;
            [y,t,x]=lsim(
                sys,
                r*ones( size(t) ),
                t,
                [initPozicia;0;initUhol;0]
            );

            disp([y,t,x]);
        ";

        $params = $request->validate([
            'M' => 'required|numeric',
            'm' => 'required|numeric',
            'b' => 'required|numeric',
            'I' => 'required|numeric',
            'g' => 'required|numeric',
            'l' => 'required|numeric',
        ]);

        $code = "M = " . $params['M'] . ";" .
                "m = " . $params['m'] . ";" .
                "b = " . $params['b'] . ";" .
                "I = " . $params['I'] . ";" .
                "g = " . $params['g'] . ";" .
                "l = " . $params['l'] . ";" .
                $code;

        return $this->run($code);
    }
}
