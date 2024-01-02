<?php

namespace App\Filament\Resources\Finance\PayrunResource\Pages;

use App\Filament\Resources\Finance\PayrunResource;
use App\Models\Attendance\AttendanceRecord;
use App\Models\Attendance\AttendanceType;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Employee\SalaryDetail;
use App\Models\Finance\ReimbursementRequest;
use App\Models\Payroll\EmployeeTaxSlabValue;
use App\Models\Payroll\PayrollPolicy;
use App\Models\Payroll\Payrun;
use App\Models\Payroll\PayrunEmployee;
use App\Models\Payroll\PayrunEmployeeAllowance;
use App\Models\Payroll\PayrunEmployeeDeduction;
use App\Models\Payroll\PayrunEmployeePayment;
use App\Models\Payroll\UserPayrollPolicy;
use App\Models\TimeOff\Leave;
use App\Models\TimeOff\LeaveDate;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Throwable;

class ComparePayrun extends Page
{
    protected static string $resource = PayrunResource::class;

    protected static string $view = 'filament.resources.finance.payrun-resource.pages.compare-payrun';
    public $records;
    public $payruns;
    public function mount()
    {
        try {
            // Validate the value...
        $this->records = unserialize($_GET['records']);
        $this->payruns = collect();
        if($this->records){


        foreach ($this->records as $payrun) {
            $payrun=Payrun::find($payrun);
            $currenctPayrun = array();


$payrunId=$payrun->id;
    $payrollPolicyId = $payrun->payroll_policy_id;

        // get user related to the payroll policy
        $users = PayrunEmployee::where('payrun_id', $payrunId)->pluck('id');

 $payrunTotalAllowanceAmount=0;
 $payrunTotalDeductionAmount=0;
        //create payrunEmployee who are related to the payroll policy
        foreach ($users as $userId) {
            // $content=PayrollPayslipTemplate::where('id',4)->pluck('content')[0];
            // $employeeName=User::where('id',2)->pluck('name')[0];
            // $companyName=Company::where('id',1)->pluck('name')[0];
            // $placeholder = array(
            //     '{companyName}' => $companyName,
            //     '{employeeName}' => $employeeName,

            // );
            // $pdfContent= str_replace(array_keys($placeholder), $placeholder,  $content);
            $empId = $userId;

            $payrunEmployee = PayrunEmployee::find($empId);
            $totalWorkingHour = $payrunEmployee->total_working_hours;
            $totalUnPaidLeaveHour = $payrunEmployee->total_unpaid_leave;
            $totalPaidLeaveHour = $payrunEmployee->total_paid_leave;
            $grossSalary = $payrunEmployee->gross_salary;
            $company = Employee::where('user_id', $payrunEmployee->user_id)->with('company')->get();
            //company currency
            $companyCurrency = $company ? $company[0]->company->currency : '';

                $currenctPayrun['companyCurrency']=$companyCurrency;
            //employee currency
            $employeeCurrency = SalaryDetail::where('user_id', $payrunEmployee->user_id)->get();
            $employeeCurrency = $employeeCurrency ? $employeeCurrency[0]->currency : '';
            $payrunEmployeePayment = PayrunEmployeePayment::where('payrun_employee_id', $empId)->get();
            if ($payrunEmployeePayment) {
                $netPayAmount = $payrunEmployeePayment[0]->net_pay;
                $dateOfPayment = Carbon::create($payrunEmployeePayment[0]->created_at)->toDateString();
            }
            $company = Employee::where('user_id', $payrunEmployee->user_id)->with('company')->get();
            if ($company) {
                $companyName = $company[0]->company->name;
                $companyRegistrationId = $company[0]->company->registration_id;
            }
            $employeeName = User::find($payrunEmployee->user_id)->name;


            $payrun = Payrun::find($payrunEmployee->payrun_id);

            $payrollPolicyId = $payrun->payroll_policy_id;

            // get user related to the payroll policy
            $users = UserPayrollPolicy::where('payroll_policy_id', $payrollPolicyId)->pluck('user_id');

            //create payrunEmployee who are related to the payroll policy
            // foreach ($users as $userId) {
            $userId = $payrunEmployee->user_id;
            $attendanceRecords = AttendanceRecord::where('user_id', $userId)->get();
            $totalWorkHours = 0;
            $totalPaidLeaveHours = 0;
            $totalUnPaidLeaveHours = 0;
            $totalPaidBreakHours = 0;
            $totalUnPaidBreakHours = 0;

            // TOTAL PAID LEAVE

            $totalPaidLeave = 0;
            $totalUnPaidLeave = 0;
            $workingHourLimit = 0;
            $overTimeHour = 0;
            $leaveCounts = Leave::where('user_id', $userId)->whereBetween(
                'created_at',
                [
                    Carbon::create($payrun->start),
                    Carbon::create($payrun->end)
                ]
            )->where('status', 'approved')->get();

            //  CALCULATE GROSS SALARY

            $companyId = Employee::where('user_id', $userId)->get()[0]->company_id;
            //    calculate work hour limit
            $overTimeData = Payrun::where('id', $payrun->id)->with('payrollPolicy.overTimeRate.workTime')->get();
            if ($overTimeData) {
                $overTimePercentage = $overTimeData[0]->payrollPolicy->overTimeRate->percentage;
                $workingHourLimit = $overTimeData[0]->payrollPolicy->overTimeRate->workTime->allowed_hour;
            }
            // unpaid leave Hours
            foreach ($leaveCounts->where('is_paid', false) as $leaveCount) {
                $totalUnPaidLeave = $leaveCount->days_taken;
                $totalUnPaidLeaveHours = $totalUnPaidLeave * $workingHourLimit;
            }
            // paid leave Hours
            foreach ($leaveCounts->where('is_paid', true) as $leaveCount) {
                $totalPaidLeave = $leaveCount->days_taken;
                $totalPaidLeaveHours = $totalPaidLeave * $workingHourLimit;
            }

            //calculate salary per hour
            $salaryDetail = SalaryDetail::where('user_id', $userId)->with('paymentInterval')->get();
            $salaryPerHour = 0;
            if ($salaryDetail) {
                if ($salaryDetail[0]->paymentInterval) {
                    if ($salaryDetail[0]->paymentInterval->name == 'Hourly') {
                        $salaryPerHour = $salaryDetail[0]->amount;
                    }
                    if ($salaryDetail[0]->paymentInterval->name == 'Daily') {
                        $salaryPerHour = $salaryDetail[0]->amount / $workingHourLimit;
                    }
                    if ($salaryDetail[0]->paymentInterval->name == 'Weekly') {
                        $salaryPerHour = $salaryDetail[0]->amount / ($workingHourLimit * 7);
                    }
                    if ($salaryDetail[0]->paymentInterval->name == 'Biweekly') {
                        $salaryPerHour = $salaryDetail[0]->amount / ($workingHourLimit * 7 * 2);
                    }
                    if ($salaryDetail[0]->paymentInterval->name == 'Monthly') {
                        if ($payrun->payment_interval == 'monthly') {
                            $daysInMonth = Carbon::create($payrun->start)->daysInMonth;
                            $salaryPerHour = $salaryDetail[0]->amount / ($workingHourLimit * $daysInMonth);
                        } else {
                            $daysInMonth = Carbon::create($payrun->start)->daysInMonth;
                            $salaryPerHour = $salaryDetail[0]->amount / ($workingHourLimit * $daysInMonth);
                        }
                    }
                    if ($salaryDetail[0]->paymentInterval->name == 'Yearly') {
                        $daysInYear = Carbon::create($payrun->start)->daysInYear;
                        $salaryPerHour = $salaryDetail[0]->amount / ($workingHourLimit * $daysInYear);
                    }
                }
            }

            if (!$employeeCurrency == $companyCurrency) {
                $salaryPerHour = $salaryPerHour * self::convertCurrency($companyCurrency, $employeeCurrency, 1);
}
            // Get user and associated team
            $user = User::where('id', $userId)->with(['team.policies.policyleaveTypes', 'employee.company.policies.policyleaveTypes'])->first();
            //    dd($user);
            // If no associated user or team, return empty array


            $team = $user->team;
            // dd($team);
            // Check if team has policies
            $teamPolicies = $team->policies->where('is_active', true);
            if (!$teamPolicies->isEmpty()) {
                $policyStartDate = $teamPolicies->first()->start_date;
                // If team has active policies, get associated policy leave types
                $policyLeaveTypes = $teamPolicies->flatMap->policyleaveTypes;
            } else {
                // If team has no active policies, get company's active policies
                $companyPolicies = optional($user->employee->company)->policies->where('is_active', true);


                // Get policy leave types associated with the company's policies
                $policyLeaveTypes = $companyPolicies->flatMap->policyleaveTypes;
            }

            // Get user's gender
            $userGenderId = $user->employee->gender_id;

            // Prepare array for select options
            //calculate paid leave

            $options = [];
            $paidLeaves = 0;
            $unPaidLeaves = 0;

            foreach ($policyLeaveTypes as $policyLeaveType) {
                if ($policyLeaveType->is_paid && $policyLeaveType->negative_leave_balance == false) {

                    $paidLeavesNotNegativeLeaveBalance = LeaveDate::whereBetween(
                        'leave_date',
                        [
                            Carbon::create($payrun->start),
                            Carbon::create($payrun->end)
                        ]
                    )->whereHas('leave', function ($query) use ($userId, $policyLeaveType) {
                        $query->where('user_id', $userId)->where('status', 'approved')->whereHas('leaveType', function ($query) use ($policyLeaveType) {
                            $query->where('id', $policyLeaveType->id);
                        });
                    })->get();
                    if ($paidLeavesNotNegativeLeaveBalance && $paidLeavesNotNegativeLeaveBalance->count() > 0) {
                        $paidLeaves += $paidLeavesNotNegativeLeaveBalance->count();
                    }
                }
                if ($policyLeaveType->is_paid && $policyLeaveType->negative_leave_balance) {

                    $paidLeavesNegativeLeaveBalance = LeaveDate::whereBetween(
                        'leave_date',
                        [
                            Carbon::create($payrun->start),
                            Carbon::create($payrun->end)
                        ]
                    )->whereHas('leave', function ($query) use ($userId, $policyLeaveType) {
                        $query->where('user_id', $userId)->where('status', 'approved')->whereHas('leaveType', function ($query) use ($policyLeaveType) {
                            $query->where('id', $policyLeaveType->id);
                        });
                    })->get();
                    if ($paidLeavesNegativeLeaveBalance && $paidLeavesNegativeLeaveBalance->count() > 0) {
                        $paidLeavesNegativeLeaveBalanceCount = $paidLeavesNegativeLeaveBalance->count();
                        if ($paidLeavesNegativeLeaveBalanceCount > $policyLeaveType->days_allowed) {

                            $paidLeaves += $policyLeaveType->days_allowed;
                            $unPaidLeaves += $paidLeavesNegativeLeaveBalanceCount - $policyLeaveType->days_allowed;
                        }
                    }
                }
                if ($policyLeaveType->is_paid == false) {

                    $paidLeavesNotNegativeLeaveBalance = LeaveDate::whereBetween(
                        'leave_date',
                        [
                            Carbon::create($payrun->start),
                            Carbon::create($payrun->end)
                        ]
                    )->whereHas('leave', function ($query) use ($userId, $policyLeaveType) {
                        $query->where('user_id', $userId)->where('status', 'approved')->whereHas('leaveType', function ($query) use ($policyLeaveType) {
                            $query->where('id', $policyLeaveType->id);
                        });
                    })->get();

                    if ($paidLeavesNotNegativeLeaveBalance && $paidLeavesNotNegativeLeaveBalance->count() > 0) {
                        $unPaidLeaves += $paidLeavesNotNegativeLeaveBalance->count();
                    }
                }
            }



            $paidLeaveHour = $paidLeaves * $workingHourLimit;
            $paidLeaveAmount = $paidLeaveHour * $salaryPerHour;
            $totalPaidLeave = $paidLeaves;

            //calculate unpaid leave

            $totalUnPaidLeave = $unPaidLeaves;
            // convert employee timezone into company timezone
            foreach ($attendanceRecords as $attendanceRecord) {
                $attendanceRecord->in = Carbon::create($attendanceRecord->in)->tz(Company::find($companyId)->timezone);
            }
            //TOTAL WORK HOURS
            foreach ($attendanceRecords->where('attendance_type_id', AttendanceType::where('name', 'work')->pluck('id')[0])
                ->whereBetween(
                    'in',
                    [
                        Carbon::create($payrun->start),
                        Carbon::create($payrun->end)
                    ]
                )->where('status', 'approved') as $attendanceRecord) {

                // calculate overtime hour nd total hour
                if ($attendanceRecord->total_hours > $workingHourLimit) {

                    $overTimeHour = $overTimeHour + ($attendanceRecord->total_hours - $workingHourLimit);
                    $totalWorkHours = $totalWorkHours + $workingHourLimit;
                } else {
                    $totalWorkHours = $totalWorkHours + $attendanceRecord->total_hours;
                }
            }

            //TOTAL PAID BREAK HOURS
            foreach ($attendanceRecords->where('attendance_type_id', AttendanceType::where('name', 'paid break')->get()[0]->id)
                ->whereBetween(
                    'in',
                    [
                        Carbon::create($payrun->start),
                        Carbon::create($payrun->end)
                    ]
                )->where('status', 'approved') as $attendanceRecord) {
                $totalPaidBreakHours = $totalPaidBreakHours + $attendanceRecord->total_hours;
            }

            //TOTAL UNPAID BREAK HOURS
            foreach ($attendanceRecords->where('attendance_type_id', AttendanceType::where('name', 'paid break')->get()[0]->id)
                ->whereBetween(
                    'in',
                    [
                        Carbon::create($payrun->start),
                        Carbon::create($payrun->end)
                    ]
                )->where('status', 'approved') as $attendanceRecord) {
                $totalUnPaidBreakHours = $totalUnPaidBreakHours + $attendanceRecord->total_hours;
            }

            // working time amount
            $workingTimeAmount = $salaryPerHour * $totalWorkHours;

            //overtime amount
            $overTimeAmount = $overTimeHour <= 0 ? 0 : ($overTimeHour * $salaryPerHour) + (($overTimePercentage / 100) * $salaryPerHour*$overTimeHour);
            $overTimeRate=$salaryPerHour+(($overTimePercentage / 100) * $salaryPerHour);
            $unPaidLeaveAmount = $totalUnPaidLeave * 8 * $salaryPerHour;


            // gross salary
            $grossSalary = $workingTimeAmount + $overTimeAmount +$paidLeaveAmount;

            // create payrun employe when payrun created


            $PayrollPolicy = PayrollPolicy::where('id', Payrun::find($payrunEmployee->payrun_id)->payroll_policy_id)->with('payrollPolicyAllowance.allowance', 'payrollPolicyDeduction.deduction', 'taxSlab.taxSlabValue')->get();
            // dd($PayrollPolicy);
            if ($PayrollPolicy->isNotEmpty()) {

                $allowances = $PayrollPolicy[0]->payrollPolicyAllowance;
                $deductions = $PayrollPolicy[0]->payrollPolicyDeduction;
                $taxSlab = $PayrollPolicy[0]->taxSlab;
                $taxSlabValues = $PayrollPolicy[0]->taxSlab->taxSlabValue;
            }
            $payrunEmployeeId = $payrunEmployee->id;
            $taxable = 0;
            $amount = 0;
            $monthTaxable = 0;
            $monthTax = 0;
            $employeeContributionAmount = 0;
            $employeeContribution = 0;
            $employerContribution = 0;
            $workmanPercentage = 0;
            $workmanAmount = 0;
            $netPay = 0;
            $grossSalary = $payrunEmployee->gross_salary;
            $amount = $grossSalary;
            // get allowance for employee
            $allowances = PayrunEmployeeAllowance::where('payrun_employee_id', $payrunEmployeeId)->with('allowance')->get();
            $deductions = PayrunEmployeeDeduction::where('payrun_employee_id', $payrunEmployeeId)->with('deduction')->get();
            $payrunId = $payrunEmployee->payrun_id;
            $payrun = Payrun::find($payrunId);
            // $attendanceRecords = AttendanceRecord::where('user_id', PayrunEmployee::find($payrunEmployeeId)->user_id)->get();


            //payrun currency
            $currency = $payrun ? $payrun->currency : '';
            $currenctPayrun['currency']=$currency;

            if ($payrunId) {
                $PayrollPolicy = PayrollPolicy::where('id', Payrun::find($payrunId)->payroll_policy_id)->with('socialSecurity')->get();
                if ($PayrollPolicy) {
                    // get taxslab values
                    $taxSlabValues = EmployeeTaxSlabValue::where('payrun_employee_id', $payrunEmployeeId)->get();
                    $allowanceList = [];
                    $totalAllowanceAmount = 0;

                    if ($taxSlabValues) {
                        // calculate allowance if before tax
                    $currenctPayrun['totalAllowanceCount']=count($allowances);

                        foreach ($allowances as $allowance) {
                            $currenctAllowance = 0;
                            $currenctAllowanceCount = 0;
                            // check before tax
                            // check frequency and payment interval monthly
                            if ($allowance->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                // looping based on the allowance occurrence
                                for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {

                                    $allowancePercentage = $allowance->percentage;
                                    $allowanceAmount = $allowance->amount;
                                    $currenctAllowance += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $totalAllowanceAmount += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $currenctAllowanceCount += 1;
                                }
                            } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                // looping based on the allowance occurrence
                                for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {
                                    $allowancePercentage = $allowance->percentage;
                                    $allowanceAmount = $allowance->amount;
                                    $currenctAllowance += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $totalAllowanceAmount += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);

                                    $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $currenctAllowanceCount += 1;
                                }
                            } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                // looping based on the allowance occurrence
                                for ($occurrence = 1; $occurrence <= $allowance->occurrence * 2; $occurrence++) {
                                    $allowancePercentage = $allowance->percentage;
                                    $allowanceAmount = $allowance->amount;
                                    $currenctAllowance += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $totalAllowanceAmount += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);

                                    $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $currenctAllowanceCount += 1;
                                }
                            }
                            if ($allowance->frequency == 'daily') {
                                // looping based on the allowance occurrence
                                for ($occurrence = 1; $occurrence <= $allowance->occurrence * $attendanceRecords->where('attendance_type_id', AttendanceType::where('name', 'work')->pluck('id')[0])
                                    ->whereBetween(
                                        'in',
                                        [
                                            Carbon::create($payrun->start),
                                            Carbon::create($payrun->end)
                                        ]
                                    )->where('status', 'approved')->count(); $occurrence++) {

                                    $allowancePercentage = $allowance->percentage;
                                    $allowanceAmount = $allowance->amount;
                                    $currenctAllowance += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $totalAllowanceAmount += $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);

                                    $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    $currenctAllowanceCount += 1;
                                }
                            }

                            $allowanceList[$allowance->allowance->name] = [$currenctAllowance, $currenctAllowanceCount];
                        }
                        $deductionList = [];
                        $totalDeductionAmount = 0;

                        // calculate deduction if before tax
                    $currenctPayrun['totalDeductionCount']=count($deductions);

                        foreach ($deductions as $deduction) {
                            $currenctDeduction = 0;
                            $totalDeductionCount = 0;

                            // check frequency and payment interval monthly
                            if ($deduction->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                // looping based on the deduction occurrence
                                for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                    $deductionPercentage = $deduction->percentage;
                                    $deductionAmount = $deduction->amount;
                                    $currenctDeduction += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionAmount += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);

                                    $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionCount += 1;
                                }
                            } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                // looping based on the deduction occurrence
                                for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                    $deductionPercentage = $deduction->percentage;
                                    $deductionAmount = $deduction->amount;
                                    $currenctDeduction += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionAmount += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);

                                    $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionCount += 1;
                                }
                            } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                // looping based on the deduction occurrence
                                for ($occurrence = 1; $occurrence <= $deduction->occurrence * 2; $occurrence++) {
                                    $deductionPercentage = $deduction->percentage;
                                    $deductionAmount = $deduction->amount;
                                    $currenctDeduction += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionAmount += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);

                                    $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionCount += 1;
                                }
                            }
                            if ($deduction->frequency == 'daily') {
                                // looping based on the deduction occurrence
                                for ($occurrence = 1; $occurrence <= $deduction->occurrence * $attendanceRecords->where('attendance_type_id', AttendanceType::where('name', 'work')->pluck('id')[0])
                                    ->whereBetween(
                                        'in',
                                        [
                                            Carbon::create($payrun->start),
                                            Carbon::create($payrun->end)
                                        ]
                                    )->where('status', 'approved')->count(); $occurrence++) {
                                    $deductionPercentage = $deduction->percentage;
                                    $deductionAmount = $deduction->amount;
                                    $currenctDeduction += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionAmount += $deductionAmount + (($deductionPercentage / 100) * $grossSalary);

                                    $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    $totalDeductionCount += 1;
                                }
                            }
                            $deductionList[$deduction->deduction->name] = [$currenctDeduction, $totalDeductionCount];
                        }

                    }
                    $payrunTotalDeductionAmount+=$totalDeductionAmount;
                    $payrunTotalAllowanceAmount+=$totalAllowanceAmount;
                    $currenctPayrun['payrunTotalDeductionAmount']=$payrunTotalDeductionAmount;
                    $currenctPayrun['payrunTotalAllowanceAmount']=$payrunTotalAllowanceAmount;

                }
            }
            $reimbursementList = [];
            // Reimbursement request
            $totalReimbursementAmount=0;
            $totalReimbursementCount=0;
            $reimbursementRequests = ReimbursementRequest::where('requested_by', $userId)->where('status', 'approved')->whereBetween(
                'created_at',
                [
                    Carbon::create($payrun->start),
                    Carbon::create($payrun->end)
                ]
            )->get();
            $totalReimbursementCount+=Count($reimbursementRequests);
            $reimbursementAmount = 0;
            if ($reimbursementRequests) {
                foreach ($reimbursementRequests as $reimbursementRequest) {

                    $currentReimbursementAmount = 0;
                    $reimbursementAmount += $reimbursementRequest->amount;
                    $reimbursementList[$reimbursementRequest->name] = $reimbursementRequest->amount;
                }
            }
            $totalReimbursementAmount+=$reimbursementAmount;
            $currenctPayrun['totalReimbursementAmount']= $totalReimbursementAmount;
            $currenctPayrun['totalReimbursementCount']= $totalReimbursementCount;

        }
            // total gross salary
            $totalGrossSalary = PayrunEmployee::where('payrun_id', $payrun->id)->sum('gross_salary');

            // total tax deduction
            $totalTax = PayrunEmployeePayment::whereHas('payrunEmployee', function ($query) use ($payrun) {
                return $query->where('payrun_id', $payrun->id);
            })->sum('tax');

            // total net pay
            $totalNet = PayrunEmployeePayment::whereHas('payrunEmployee', function ($query) use ($payrun) {
                return $query->where('payrun_id', $payrun->id);
            })->sum('net_pay');

            // calculate social security
            $totalEmployeeSocialSecurity = PayrunEmployeePayment::whereHas('payrunEmployee', function ($query) use ($payrun) {
                return $query->where('payrun_id', $payrun->id);
            })->sum('employee_social_security');

            $totalEmployerSocialSecurity = PayrunEmployeePayment::whereHas('payrunEmployee', function ($query) use ($payrun) {
                return $query->where('payrun_id', $payrun->id);
            })->sum('employer_social_security');
            $currenctPayrun['name'] = $payrun->name;
            $currenctPayrun['totalGrossSalary'] = $totalGrossSalary;
            $currenctPayrun['totalTax'] = $totalTax;
            $currenctPayrun['totalNet'] = $totalNet;
            $currenctPayrun['totalEmployeeSocialSecurity'] = $totalEmployeeSocialSecurity;
            $currenctPayrun['totalEmployerSocialSecurity'] = $totalEmployerSocialSecurity;
            $this->payruns->push($currenctPayrun);
        }
        // dd($this->payruns);
    }
}catch (Throwable $e) {
    abort(404, 'Try Again');
}
}

public static function convertCurrency($want, $have, $amount)
{

    $req_url = "https://v6.exchangerate-api.com/v6/ac527beaebc0020b0f74074f/pair/".$have."/".$want."/".$amount;
    $response_json = file_get_contents($req_url);

    // Continuing if we got a result
    if (false !== $response_json) {
        // Try/catch for json_decode operation
        try {

            // Decoding
            $response = json_decode($response_json);

            // Check for success
            if ('success' === $response->result) {

                return $response->conversion_result;
            }
        } catch (Exception $e) {
            // Handle JSON parse error...
            Notification::make()
            ->title('Currency Convertion is not working')
            ->success()
            ->send();
        }
    }

}
}
