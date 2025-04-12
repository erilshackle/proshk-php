<?php include '@layout.php';

use App\Models\User;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $validation = new Validator($_POST, [
        'email' => 'email|required',
        'password' => 'required',
    ]);

    if ($validation->fails()) {
        flash('error', $validation->firstError());
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];


    $user = new User([
        'email'=> $email,
        'password' => bcrypt($password),
    ]);

    

    redirect();
}

?>



<div class="container mt-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-6">
            <div class="card px-5 py-5" id="form1">
                <div
                    class="alert alert-warning"
                    role="alert">
                    <strong>Error</strong>
                </div>

                <form method="post" class="row g-3 needs-validation" novalidate>
                    <div class="col-md-12">
                        <label for="validationCustom01" class="form-label">Email</label>
                        <input type="email" class="form-control" id="validationCustom01" placeholder="user@mail" required>
                        <div class="valid-feedback">
                            Looks good!
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label for="validationCustom02" class="form-label">Password</label>
                        <input type="password" class="form-control" id="validationCustom02" value="" required>
                        <div class="valid-feedback">
                            Looks good!
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                            <label class="form-check-label" for="invalidCheck">
                                Agree to terms and conditions
                            </label>
                            <div class="invalid-feedback">
                                You must agree before submitting.
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>