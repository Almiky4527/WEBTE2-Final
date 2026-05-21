<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\OctaveController;

class AnimController extends Controller
{
    private function run(string $code)
    {
        $payload = ['code' => $code];
        $post_request = new Request($payload);
        $post_request->setLaravelSession(request()->session());

        $octave_controller = app()->make(OctaveController::class);

        return $octave_controller->evaluate($post_request);
    }

    private function parseSeries($response)
    {
        $data = $response->getData(true);

        if (empty($data['success'])) {
            return $response;
        }

        $output = $data['output'] ?? '';
        $jsonStart = strpos($output, '{');
        if ($jsonStart === false) {
            return response()->json([
                'success' => false,
                'error'   => 'Octave produced no series output',
                'stderr'  => $data['stderr'] ?? '',
                'output'  => $output,
            ], 500);
        }

        $series = json_decode(substr($output, $jsonStart), true);
        if (!is_array($series) || !isset($series['t'], $series['y'], $series['x'])) {
            return response()->json([
                'success' => false,
                'error'   => 'Octave returned malformed series',
                'stderr'  => $data['stderr'] ?? '',
                'output'  => $output,
            ], 500);
        }

        return response()->json([
            'success' => true,
            't'       => $series['t'],
            'y'       => $series['y'],
            'x'       => $series['x'],
        ]);
    }

    /**
     * Run calculations for ball animation.
     *
     * Returns time series sampled at dt over [0, t_end] as JSON vectors.
     */
    public function run_ball(Request $request)
    {
        $params = $request->validate([
            'm'        => 'required|numeric',
            'R'        => 'required|numeric',
            'g'        => 'required|numeric',
            'J'        => 'required|numeric',
            'r'        => 'nullable|numeric',
            'pos0'     => 'nullable|numeric',
            'angle0'   => 'nullable|numeric',
            't_end'    => 'nullable|numeric|min:0.1|max:30',
            'dt'       => 'nullable|numeric|min:0.005|max:0.5',
        ]);

        $r      = $params['r']      ?? 0.25;
        $pos0   = $params['pos0']   ?? 0.0;
        $angle0 = $params['angle0'] ?? 0.0;
        $t_end  = $params['t_end']  ?? 5.0;
        $dt     = $params['dt']     ?? 0.02;

        $assigns = sprintf(
            "m=%.10g; R=%.10g; g=%.10g; J=%.10g; r_ref=%.10g; pos0=%.10g; angle0=%.10g; t_end=%.10g; dt=%.10g;",
            $params['m'], $params['R'], $params['g'], $params['J'],
            $r, $pos0, $angle0, $t_end, $dt
        );

        $code = $assigns . <<<'OCT'
            H = -m*g/(J/(R^2)+m);
            A = [0 1 0 0; 0 0 H 0; 0 0 0 1; 0 0 0 0];
            B = [0;0;0;1];
            C = [1 0 0 0];
            D = [0];
            K = place(A,B,[-2+2i,-2-2i,-20,-80]);
            N = -inv(C*inv(A-B*K)*B);
            sys = ss(A-B*K, B*N, C, D);
            t = 0:dt:t_end;
            [y,t,x] = lsim(sys, r_ref*ones(size(t)), t, [pos0;0;angle0;0]);
            result = struct('t', t(:)', 'y', y, 'x', x);
            disp(jsonencode(result));
        OCT;

        return $this->parseSeries($this->run($code));
    }

    /**
     * Run calculations for pendulum animation.
     *
     * Returns time series sampled at dt over [0, t_end] as JSON vectors.
     */
    public function run_pendulum(Request $request)
    {
        $params = $request->validate([
            'M'        => 'required|numeric',
            'm'        => 'required|numeric',
            'b'        => 'required|numeric',
            'I'        => 'required|numeric',
            'g'        => 'required|numeric',
            'l'        => 'required|numeric',
            'r'        => 'nullable|numeric',
            'pos0'     => 'nullable|numeric',
            'angle0'   => 'nullable|numeric',
            't_end'    => 'nullable|numeric|min:0.1|max:60',
            'dt'       => 'nullable|numeric|min:0.005|max:0.5',
        ]);

        $r      = $params['r']      ?? 0.2;
        $pos0   = $params['pos0']   ?? 0.0;
        $angle0 = $params['angle0'] ?? 0.0;
        $t_end  = $params['t_end']  ?? 10.0;
        $dt     = $params['dt']     ?? 0.05;

        $assigns = sprintf(
            "M=%.10g; m=%.10g; b=%.10g; I=%.10g; g=%.10g; l=%.10g; r_ref=%.10g; pos0=%.10g; angle0=%.10g; t_end=%.10g; dt=%.10g;",
            $params['M'], $params['m'], $params['b'], $params['I'], $params['g'], $params['l'],
            $r, $pos0, $angle0, $t_end, $dt
        );

        $code = $assigns . <<<'OCT'
            p = I*(M+m)+M*m*l^2;
            A = [0 1 0 0; 0 -(I+m*l^2)*b/p (m^2*g*l^2)/p 0; 0 0 0 1; 0 -(m*l*b)/p m*g*l*(M+m)/p 0];
            B = [0; (I+m*l^2)/p; 0; m*l/p];
            C = [1 0 0 0; 0 0 1 0];
            D = [0; 0];
            K = lqr(A,B,C'*C,1);
            Ac = A-B*K;
            N = -inv(C(1,:)*inv(A-B*K)*B);
            sys = ss(Ac,B*N,C,D);
            t = 0:dt:t_end;
            [y,t,x] = lsim(sys, r_ref*ones(size(t)), t, [pos0;0;angle0;0]);
            result = struct('t', t(:)', 'y', y, 'x', x);
            disp(jsonencode(result));
        OCT;

        return $this->parseSeries($this->run($code));
    }
}
