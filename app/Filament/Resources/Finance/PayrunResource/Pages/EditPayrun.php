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
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Actions\Action;
// use Goutte\Client;
class EditPayrun extends EditRecord
{
    protected static string $resource = PayrunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {

        if (isset($data['month'])) {
            $end = Carbon::create($data['month'])->endOfMonth()->toDateString();
            $data['end'] = $end;
            $data['start'] = $data['month'];
        }
        if($data['status']!='completed'){

            $data['is_approved']=False;
        }
        return $data;
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {

        if ($data['payment_interval'] == 'monthly') {

            $data['month'] = $data['start'];
            $data['start'] = '';
            $data['end'] = '';
        }


        return $data;
    }
    protected $listeners = ['refresh' => 'refreshForm'];

    // generate unique payrun employee id
    public function generateUniqueId($empId, $payrunId)
    {
        $payrunString = 'EMP';
        return $payrunId . $payrunString . $empId;
    }

//     public static function getLiberiaRate(){
//         $url ='https://www.cbl.org.lr/index.php/research/buying-selling-rates';
//         $client = new Client();
//         try {
//             // Send an HTTP GET request to the URL
//             $crawler = $client->request('GET', $url);

//             // Extract the Daily Tax Payment Rate element
//             $trTag = $crawler->filter('tr')->eq(1);
//             $updatedDate=$trTag->filter('[headers="view-field-content-post-date-table-column"]')->text();
// $buyingRate=$trTag->filter('[headers="view-field-buying-us-table-column"]')->text();
// $sellingRate=$trTag->filter('[headers="view-field-selling-us-table-column"]')->text();
// $data=collect();
// $data->push(['date'=>$updatedDate,'buying_rate'=>$buyingRate,'selling_rate'=>$sellingRate]);
// return $data;
//             // Get the text content of the element
//             // $dailyTaxPaymentRate = $dailyTaxPaymentRateElement->text();

//             // return $dailyTaxPaymentRate;
//         } catch (Exception $e) {

//             Notification::make()
//             ->title('Currency Convertion is not working')
//             ->success()
//             ->send();
//             return 'Error: ' . $e->getMessage();
//         }
//     }
    public static function convertCurrency($want, $have, $amount)
    {

        $req_url = "https://v6.exchangerate-api.com/v6/ac527beaebc0020b0f74074f/pair/" . $have . "/" . $want . "/" . $amount;
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
        // $client = new \GuzzleHttp\Client();

        // $response = $client->request('GET', 'https://currency-converter-by-api-ninjas.p.rapidapi.com/v1/convertcurrency?have='.$have.'&want='.$want.'&amount='.$amount, [
        //     'headers' => [
        //         'X-RapidAPI-Host' => 'currency-converter-by-api-ninjas.p.rapidapi.com',
        //         'X-RapidAPI-Key' => '0188cfa695msh42c309b4263b606p13bf7djsn29da431c7e94',
        //     ],
        // ]);

        // $convertedAmount = $response->getBody()->getContents();
        // return json_decode($convertedAmount)->new_amount;
    }


    public function refreshForm()
    {
        // self::getLiberiaRate();
        // Runs after the form fields are saved to the database.
        $payrun = $this->getRecord();
        $payrollPolicyId = $payrun->payroll_policy_id;
        $currencyRate=$payrun->currency_rate;

        PayrunEmployee::where('payrun_id', $payrun->id)->delete();
        // get user related to the payroll policy
        $users = UserPayrollPolicy::where('payroll_policy_id', $payrollPolicyId)->pluck('user_id');

        //create payrunEmployee who are related to the payroll policy
        foreach ($users as $userId) {
            $company = Employee::where('user_id', $userId)->with('company')->get();
            //company currency
            $companyCurrency = $company ? $company[0]->company->currency : '';
            //employee currency
            $employeeCurrency = SalaryDetail::where('user_id', $userId)->get();
            $employeeCurrency = $employeeCurrency ? $employeeCurrency[0]->currency : '';
            $attendanceRecords = AttendanceRecord::where('user_id', $userId)->get();
            $totalWorkHours = 0;
            $totalPaidBreakHours = 0;
            $totalUnPaidBreakHours = 0;

            // TOTAL PAID LEAVE

            $totalPaidLeave = 0;
            $totalUnPaidLeave = 0;
            $workingHourLimit = 0;
            $overTimeHour = 0;
            $leaveCounts = Leave::where('user_id', $userId)->get();
            foreach ($leaveCounts as $leaveCount) {
                $totalUnPaidLeave = $leaveCount->days_taken;
            }

            //  CALCULATE GROSS SALARY

            $companyId = Employee::where('user_id', $userId)->get()[0]->company_id;
            //    calculate work hour limit
            $overTimeData = Payrun::where('id', $payrun->id)->with('payrollPolicy.overTimeRate.workTime')->get();
            if ($overTimeData) {
                $overTimePercentage = $overTimeData[0]->payrollPolicy->overTimeRate->percentage;
                $workingHourLimit = $overTimeData[0]->payrollPolicy->overTimeRate->workTime->allowed_hour;
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
            $overTimeAmount = $overTimeHour <= 0 ? 0 : ($overTimeHour * $salaryPerHour) + (($overTimePercentage / 100) * $salaryPerHour * $overTimeHour);

            // $unPaidLeaveAmount = $totalUnPaidLeave * 8 * $salaryPerHour;


            // gross salary
            $grossSalary = $workingTimeAmount + $overTimeAmount + $paidLeaveAmount;

            // create payrun employe when payrun created
            $payrunEmployee = PayrunEmployee::create(
                [
                    'user_id' => $userId,
                    'payrun_id' => $payrun->id,
                    'payrun_employee_id' => $this->generateUniqueId($userId, $payrun->payrun_id),
                    'total_working_hours' => $totalWorkHours + $overTimeHour,
                    'total_paid_leave' => $totalPaidLeave,
                    'total_unpaid_leave' => $totalUnPaidLeave,
                    'gross_salary' => $grossSalary < 0 ? 0 : round($grossSalary, 2)
                    // 'include_in_payrun'

                ]
            );

            $PayrollPolicy = PayrollPolicy::where('id', Payrun::find($payrunEmployee->payrun_id)->payroll_policy_id)->with('payrollPolicyAllowance.allowance', 'payrollPolicyDeduction.deduction', 'taxSlab.taxSlabValue')->get();
            // dd($PayrollPolicy);
            if ($PayrollPolicy->isNotEmpty()) {

                $allowances = $PayrollPolicy[0]->payrollPolicyAllowance;
                $deductions = $PayrollPolicy[0]->payrollPolicyDeduction;
                $taxSlab = $PayrollPolicy[0]->taxSlab;
                $taxSlabValues = $PayrollPolicy[0]->taxSlab->taxSlabValue;
                // create PayrunEmployeeAllowance related to the employee
                foreach ($allowances as $allowance) {
                    PayrunEmployeeAllowance::create([
                        'payrun_employee_id' => $payrunEmployee->id,
                        'allowance_id' => $allowance->allowance->id,
                        'occurrence' => $allowance->allowance->max_count,
                        'include_in_payrun' => true,
                        'frequency' => $allowance->allowance->frequency,
                        'is_fixed' => $allowance->allowance->is_fixed,
                        'amount' => $allowance->allowance->amount,
                        'percentage' => $allowance->allowance->percentage,
                        'before_tax' => $allowance->allowance->before_tax,
                    ]);
                }
                // create PayrunEmployeeDeduction related to the employee

                foreach ($deductions as $deduction) {
                    PayrunEmployeeDeduction::create([
                        'payrun_employee_id' => $payrunEmployee->id,
                        'deduction_id' => $deduction->deduction->id,
                        'occurrence' => $deduction->deduction->max_count,
                        'include_in_payrun' => true,
                        'frequency' => $deduction->deduction->frequency,
                        'is_fixed' => $deduction->deduction->is_fixed,
                        'amount' => $deduction->deduction->amount,
                        'percentage' => $deduction->deduction->percentage,
                        'before_tax' => $deduction->deduction->before_tax,
                    ]);
                }

                // create EmployeeTaxSlab related to the employee
                // $employeeTaxSlab=EmployeeTaxSlab::create([
                // 'name'=>$taxSlab->name,
                // 'payrun_employee_id'=>$payrunEmployee->id,
                // 'tax_slab_id'=>$taxSlab->id
                //  ]);

                // create EmployeeTaxSlabvalue related to the employee

                foreach ($taxSlabValues as $taxSlabValue) {
                    EmployeeTaxSlabValue::create([
                        'payrun_employee_id' => $payrunEmployee->id,
                        'tax_slab_id' => $taxSlab->id,
                        'start' => $taxSlabValue->start,
                        'end' => $taxSlabValue->end,
                        'cal_range' => $taxSlabValue->cal_range,
                        'fixed_amount' => $taxSlabValue->fixed_amount,
                        'percentage' => $taxSlabValue->percentage,
                        'description' => $taxSlabValue->description
                    ]);
                }
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
            $allowances = PayrunEmployeeAllowance::where('payrun_employee_id', $payrunEmployeeId)->get();
            $deductions = PayrunEmployeeDeduction::where('payrun_employee_id', $payrunEmployeeId)->get();
            $payrunId = $payrunEmployee->payrun_id;
            $payrun = Payrun::find($payrunId);
            // $attendanceRecords = AttendanceRecord::where('user_id', PayrunEmployee::find($payrunEmployeeId)->user_id)->get();


            //payrun currency
            $currency = $payrun ? $payrun->currency : '';

            if ($payrunId) {
                $PayrollPolicy = PayrollPolicy::where('id', Payrun::find($payrunId)->payroll_policy_id)->with('socialSecurity')->get();
                if ($PayrollPolicy) {
                    // get taxslab values
                    $taxSlabValues = EmployeeTaxSlabValue::where('payrun_employee_id', $payrunEmployeeId)->get();

                    if ($taxSlabValues) {
                        // calculate allowance if before tax
                        foreach ($allowances as $allowance) {
                            // check before tax
                            if ($allowance->before_tax) {
                                // check frequency and payment interval monthly
                                if ($allowance->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {

                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {
                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence * 2; $occurrence++) {
                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
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
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                }
                            }
                        }

                        // calculate deduction if before tax
                        foreach ($deductions as $deduction) {
                            if ($deduction->before_tax) {
                                // check frequency and payment interval monthly
                                if ($deduction->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence * 2; $occurrence++) {
                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
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
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                }
                            }
                        }
                        // social security
                        $socialSecurity = $PayrollPolicy[0]->socialSecurity;

                        $employeeContribution = $socialSecurity->employee_contribution;
                        $employeeContributionAmount = ($employeeContribution / 100) * $grossSalary;
                        $employerContribution = $socialSecurity->employer_contribution;
                        $employerContributionAmount = ($employerContribution / 100) * $grossSalary;

                        if ($socialSecurity->before_tax) {
                            $amount -= $employeeContributionAmount;
                        }
                        // calculate taxable income
                       // calculate taxable income
                    $usdRate=1/$currencyRate;
                    if($payrun->payment_interval == 'monthly'){

                        $monthTaxable=$amount;
                        $anualTaxable = ($amount * 12)*$usdRate;
                    }
                    if($payrun->payment_interval == 'weekly'){

                        $monthTaxable=$amount;
                        $anualTaxable = ($amount *4* 12)*$usdRate;
                    }
                    if($payrun->payment_interval == 'weekly'){

                        $monthTaxable=$amount;
                        $anualTaxable = ($amount *2* 12)*$usdRate;
                    }
                        $anualTax = 0;
// $data=[];
                        foreach ($taxSlabValues as $taxSlabValue) {
                            $taxSlabValue->start = $taxSlabValue->start * $usdRate;
                            $taxSlabValue->fixed_amount = $taxSlabValue->fixed_amount * $usdRate;
                            if ($anualTaxable >= $taxSlabValue->start) {
                                $anualTax += (($anualTaxable - max($taxSlabValue->start - 1*$usdRate, 0)) * ($taxSlabValue->percentage / 100)) + $taxSlabValue->fixed_amount;
                                // $anualTaxable -= ($taxSlabValue->end - max($taxSlabValue->start - 1, 0));
                                // $data[$taxSlabValue->start]= $anualTax .' '.$taxSlabValue->fixed_amount;
                            }
                        }
                        // dd($data);
                        $monthTax = ($anualTax / $usdRate) / 12;

                        // // convert currency when employee and company currencies are not same
                        // if(!$employeeCurrency==$companyCurrency){
                        //     $monthTax = ($anualTax / 12)/self::convertCurrency($companyCurrency,$employeeCurrency,1);
                        // }
                        // calculate allowance if after tax
                        foreach ($allowances as $allowance) {
                            if (!$allowance->before_tax) {
                                // check frequency and payment interval monthly
                                if ($allowance->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {

                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence; $occurrence++) {

                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                } elseif ($allowance->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                    // looping based on the allowance occurrence
                                    for ($occurrence = 1; $occurrence <= $allowance->occurrence * 2; $occurrence++) {
                                        $allowancePercentage = $allowance->percentage;
                                        $allowanceAmount = $allowance->amount;
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
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
                                        $amount = $amount + $allowanceAmount + (($allowancePercentage / 100) * $grossSalary);
                                    }
                                }
                            }
                        }
                        // calculate deduction if after tax
                        foreach ($deductions as $deduction) {
                            if (!$deduction->before_tax) {
                                // check frequency and payment interval monthly
                                if ($deduction->frequency == 'monthly' && $payrun->payment_interval == 'monthly') {
                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'weekly') {

                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence; $occurrence++) {

                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                } elseif ($deduction->frequency == 'weekly' && $payrun->payment_interval == 'biweekly') {
                                    // looping based on the deduction occurrence
                                    for ($occurrence = 1; $occurrence <= $deduction->occurrence * 2; $occurrence++) {
                                        $deductionPercentage = $deduction->percentage;
                                        $deductionAmount = $deduction->amount;
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
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
                                        $amount = $amount - $deductionAmount - (($deductionPercentage / 100) * $grossSalary);
                                    }
                                }
                            }
                        }

                        // Reimbursement request
                        $reimbursementRequests = ReimbursementRequest::where('requested_by', $userId)->where('status', 'approved')->get();
                        $reimbursementAmount = 0;
                        if ($reimbursementRequests) {
                            foreach ($reimbursementRequests as $reimbursementRequest) {
                                $reimbursementAmount += $reimbursementRequest->amount;
                            }
                        }

                        //work man
                        $workmanPercentage = 2;
                        $workmanAmount = (2 / 100) * $grossSalary;
                        // social security after tax
                        // $employeeContributionAmount=$socialSecurity->before_tax?0:$employeeContributionAmount;
                        if (!$socialSecurity->before_tax) {
                            $netPay = $monthTaxable - $monthTax - $employeeContributionAmount;
                        } else {
                            $netPay = $monthTaxable - $monthTax;
                        }
                        // net pay
                        if(!$currency==$companyCurrency){
                            $netPay= $netPay < 0 ? 0 :$netPay*self::convertCurrency($currency,$companyCurrency,1);
                         }
                    } else {
                        Notification::make()
                            ->title('Please create tax slab for this employee')
                            ->success()
                            ->send();
                    }

                    // dd($netPay,self::convertCurrency($currency,$companyCurrency,1),round($netPay*self::convertCurrency($currency,$companyCurrency,1),2));
                    //     $set('taxable', round($monthTaxable));
                    // $set('tax', round($monthTax));
                    // $set('employee_social_security_percentage', round($employeeContribution));
                    // $set('employee_social_security', round($employeeContributionAmount));
                    // $set('employer_social_security_percentage', round($employerContribution));
                    // $set('workman_percentage', round($workmanPercentage));
                    // $set('workman', round($workmanAmount));
                    // $set('net_pay', round($netPay));
                    PayrunEmployeePayment::create([
                        'payrun_employee_id' => $payrunEmployeeId,
                        'employee_social_security' => $employeeContributionAmount < 0 ? 0 : round($employeeContributionAmount, 2),
                        'employer_social_security' => $employerContributionAmount < 0 ? 0 : round($employerContributionAmount, 2),

                        'employee_social_security_percentage' => $employeeContribution < 0 ? 0 : round($employeeContribution, 2),
                        'employer_social_security_percentage' => $employerContribution < 0 ? 0 : round($employerContribution, 2),
                        'taxable' => $monthTaxable < 0 ? 0 : round($monthTaxable, 2),
                        'tax' => $monthTax < 0 ? 0 : round($monthTax, 2),
                        'net_pay' => round($netPay,2),
                        'workman' => $workmanAmount < 0 ? 0 : round($workmanAmount, 2),
                        'workman_percentage' => $workmanPercentage < 0 ? 0 : round($workmanPercentage, 2),
                    ]);
                }
            }
        }
        // route('filament.admin.resources.employees.view', ['record' => auth()->user()->id,
        return redirect()->route('filament.admin.resources.finance.payruns.edit', ['record' => $payrun->id]);
    }
    protected function afterSave(): void
    {
        $record = $this->getRecord();
        if ($record) {
            if ($record->status == 'completed') {
                $users = User::whereHas('roles', function ($q) {
                    $q->where('name', 'Finance Admin');
                })->get();
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        Notification::make()
                            ->title('Payrun status is completed')
                            ->actions([
                                Action::make('view')
                                ->button()->url('/finance/payruns/' . $record->id . '/edit')->close()
                            ])
                            ->sendToDatabase($user);
                    }
                }
            }
        }
        // Runs after the form fields are saved to the database.
    }
}
