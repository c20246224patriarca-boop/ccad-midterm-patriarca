import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Bus, BusFront, CalendarDays, ChartNoAxesColumnIncreasing, CircleDollarSign, Ticket, UserRoundCheck, UsersRound } from 'lucide-react';
import type { ComponentType, SVGProps } from 'react';

type RevenuePoint = {
    date: string;
    total: number;
};

type TripStatus = {
    label: string;
    count: number;
    percentage: number;
    color: 'blue' | 'green' | 'yellow' | 'red';
};

type DashboardProps = {
    totalTrips: number;
    upcomingTrips: number;
    bookedTickets: number;
    grossRevenue: number;
    completedTrips: number;
    activeDrivers: number;
    availableBuses: number;
    totalPassengers: number;
    tripStatuses: TripStatus[];
    revenueData: RevenuePoint[];
    departures: {
        id: number;
        route_origin: string;
        route_destination: string;
        bus_plate: string;
        departure_date: string;
        departure_time: string;
        status: string;
    }[];
    recentBookings: {
        id: number;
        amount: number;
        passenger: string;
        route: string;
        status: string;
        created_at: string;
    }[];
};

type MetricCardProps = {
    label: string;
    value: string;
    description: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(amount);

function MetricCard({ label, value, description, icon: Icon }: MetricCardProps) {
    return (
        <section className="rounded-xl border border-gray-800 bg-[#111111] p-5 transition hover:border-gray-600">
            <div className="flex items-center justify-between gap-4">
                <p className="text-sm text-gray-400">{label}</p>
                <span className="rounded-lg bg-[#252525] p-3 text-blue-400">
                    <Icon className="size-5" aria-hidden="true" />
                </span>
            </div>
            <h2 className="mt-4 text-3xl font-bold break-words">{value}</h2>
            <p className="mt-2 text-sm text-gray-500">{description}</p>
        </section>
    );
}

export default function Dashboard({
    totalTrips,
    upcomingTrips,
    bookedTickets,
    grossRevenue,
    completedTrips,
    activeDrivers,
    availableBuses,
    totalPassengers,
    tripStatuses,
    revenueData,
    departures,
    recentBookings,
}: DashboardProps) {
    const metrics: MetricCardProps[] = [
        {
            label: 'Total trips',
            value: totalTrips.toLocaleString(),
            description: 'All recorded trips',
            icon: BusFront,
        },
        {
            label: 'Upcoming trips',
            value: upcomingTrips.toLocaleString(),
            description: 'Scheduled departures',
            icon: CalendarDays,
        },
        {
            label: 'Booked tickets',
            value: bookedTickets.toLocaleString(),
            description: 'Active reservations',
            icon: Ticket,
        },
        {
            label: 'Gross revenue',
            value: formatCurrency(grossRevenue),
            description: 'Revenue from booked tickets',
            icon: CircleDollarSign,
        },
        {
            label: 'Completed trips',
            value: completedTrips.toLocaleString(),
            description: 'Successfully completed',
            icon: ChartNoAxesColumnIncreasing,
        },
        {
            label: 'Active drivers',
            value: activeDrivers.toLocaleString(),
            description: 'Currently active drivers',
            icon: UserRoundCheck,
        },
        {
            label: 'Available buses',
            value: availableBuses.toLocaleString(),
            description: 'Inspected and not assigned to an upcoming trip',
            icon: Bus,
        },
        {
            label: 'Passengers',
            value: totalPassengers.toLocaleString(),
            description: 'Unique passenger names on tickets',
            icon: UsersRound,
        },
    ];

    const maxRevenue = Math.max(...revenueData.map((point) => point.total), 0);
    const chartPoints = revenueData.map((point, index) => ({
        ...point,
        x: 52 + (index * 620) / Math.max(revenueData.length - 1, 1),
        y: 218 - (maxRevenue > 0 ? (point.total / maxRevenue) * 178 : 0),
    }));
    const chartLine = chartPoints.map((point) => `${point.x},${point.y}`).join(' ');
    const chartArea =
        chartPoints.length > 0
            ? `M ${chartPoints[0].x},218 L ${chartPoints.map((point) => `${point.x},${point.y}`).join(' L ')} L ${chartPoints[chartPoints.length - 1].x},218 Z`
            : '';
    const statusBarColors: Record<TripStatus['color'], string> = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        yellow: 'bg-yellow-500',
        red: 'bg-red-500',
    };
    const tripStatusClasses: Record<string, string> = {
        Scheduled: 'bg-blue-500/10 text-blue-400',
        Departed: 'bg-yellow-500/10 text-yellow-400',
        Completed: 'bg-green-500/10 text-green-400',
        Cancelled: 'bg-red-500/10 text-red-400',
    };
    const bookingStatusClasses: Record<string, string> = {
        Booked: 'bg-green-500/10 text-green-400',
        Cancelled: 'bg-yellow-500/10 text-yellow-400',
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <main className="min-h-full bg-[#090909] p-4 text-white sm:p-6">
                <header className="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold">Terminal Dashboard</h1>
                        <p className="mt-2 text-gray-400">Welcome to your Bus Terminal Management System.</p>
                    </div>
                    <span className="flex items-center gap-2 text-sm text-green-400">
                        <span className="size-2 rounded-full bg-green-400" aria-hidden="true" />
                        System Online
                    </span>
                </header>

                <section aria-label="Terminal statistics" className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {metrics.map((metric) => (
                        <MetricCard key={metric.label} {...metric} />
                    ))}
                </section>

                <section className="mt-8 grid grid-cols-1 gap-5 xl:grid-cols-3">
                    <div className="rounded-xl border border-gray-800 bg-[#111111] p-5 sm:col-span-2 sm:p-6">
                        <div className="mb-5">
                            <div className="flex items-center gap-2">
                                <ChartNoAxesColumnIncreasing className="size-5 text-blue-400" aria-hidden="true" />
                                <h2 className="text-lg font-semibold">Revenue Analytics</h2>
                            </div>
                            <p className="mt-1 text-sm text-gray-400">Weekly ticket revenue overview.</p>
                        </div>

                        <div
                            role="img"
                            aria-label={`Revenue chart for the last seven days. Total revenue: ${formatCurrency(
                                revenueData.reduce((sum, point) => sum + point.total, 0),
                            )}.`}
                            className="h-64 w-full"
                        >
                            <svg viewBox="0 0 700 260" preserveAspectRatio="none" className="size-full" aria-hidden="true">
                                <defs>
                                    <linearGradient id="revenue-fill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stopColor="#3b82f6" stopOpacity="0.35" />
                                        <stop offset="100%" stopColor="#3b82f6" stopOpacity="0.02" />
                                    </linearGradient>
                                </defs>
                                {[40, 99, 159, 218].map((y, index) => (
                                    <g key={y}>
                                        <line x1="52" x2="680" y1={y} y2={y} stroke="#292929" strokeWidth="1" />
                                        <text x="44" y={y + 4} fill="#9ca3af" fontSize="10" textAnchor="end">
                                            {Math.round(maxRevenue * ((3 - index) / 3))}
                                        </text>
                                    </g>
                                ))}
                                {chartArea && <path d={chartArea} fill="url(#revenue-fill)" />}
                                <polyline
                                    points={chartLine}
                                    fill="none"
                                    stroke="#3b82f6"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="3"
                                />
                                {chartPoints.map((point) => (
                                    <g key={point.date}>
                                        <circle cx={point.x} cy={point.y} r="4" fill="#3b82f6">
                                            <title>{`${point.date}: ${formatCurrency(point.total)}`}</title>
                                        </circle>
                                        <text x={point.x} y="246" fill="#9ca3af" fontSize="11" textAnchor="middle">
                                            {point.date}
                                        </text>
                                    </g>
                                ))}
                            </svg>
                        </div>
                    </div>

                    <div className="rounded-xl border border-gray-800 bg-[#111111] p-5 sm:p-6">
                        <h2 className="text-lg font-semibold">Trip Overview</h2>
                        <p className="mb-6 text-sm text-gray-400">Trip status summary.</p>
                        <div className="space-y-6">
                            {tripStatuses.map((status) => (
                                <div key={status.label}>
                                    <div className="mb-2 flex justify-between text-sm">
                                        <span className="text-gray-300">{status.label}</span>
                                        <span>{status.count}</span>
                                    </div>
                                    <div
                                        className="h-2 w-full overflow-hidden rounded-full bg-gray-800"
                                        role="progressbar"
                                        aria-label={`${status.label} trips`}
                                        aria-valuenow={status.percentage}
                                        aria-valuemin={0}
                                        aria-valuemax={100}
                                    >
                                        <div
                                            className={`h-2 rounded-full ${statusBarColors[status.color]}`}
                                            style={{ width: `${status.percentage}%` }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mt-8 overflow-hidden rounded-xl border border-gray-800 bg-[#111111]">
                    <div className="flex items-center justify-between border-b border-gray-800 p-6">
                        <div>
                            <h2 className="text-lg font-semibold">Upcoming departures</h2>
                            <p className="text-sm text-gray-400">Next scheduled trips at this terminal.</p>
                        </div>
                        <span className="text-sm text-blue-400">{departures.length} Trips</span>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[850px] text-left text-sm">
                            <thead className="bg-[#191919] text-gray-400">
                                <tr>
                                    <th className="p-4 font-medium">Trip ID</th>
                                    <th className="p-4 font-medium">Origin</th>
                                    <th className="p-4 font-medium">Destination</th>
                                    <th className="p-4 font-medium">Bus</th>
                                    <th className="p-4 font-medium">Date</th>
                                    <th className="p-4 font-medium">Departure</th>
                                    <th className="p-4 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {departures.length > 0 ? (
                                    departures.map((trip) => (
                                        <tr key={trip.id} className="border-t border-gray-800 hover:bg-[#191919]">
                                            <td className="p-4">#{trip.id}</td>
                                            <td className="p-4">{trip.route_origin}</td>
                                            <td className="p-4">{trip.route_destination}</td>
                                            <td className="p-4">{trip.bus_plate}</td>
                                            <td className="p-4 whitespace-nowrap">{trip.departure_date}</td>
                                            <td className="p-4 whitespace-nowrap">{trip.departure_time}</td>
                                            <td className="p-4">
                                                <span
                                                    className={`rounded-full px-3 py-1 text-xs ${
                                                        tripStatusClasses[trip.status] ?? 'bg-blue-500/10 text-blue-400'
                                                    }`}
                                                >
                                                    {trip.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={7} className="p-10 text-center text-gray-500">
                                            <CalendarDays className="mx-auto mb-3 size-8" aria-hidden="true" />
                                            No upcoming departures available.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="mt-8 overflow-hidden rounded-xl border border-gray-800 bg-[#111111]">
                    <div className="border-b border-gray-800 p-6">
                        <h2 className="text-lg font-semibold">Recent bookings</h2>
                        <p className="text-sm text-gray-400">Latest passenger ticket reservations.</p>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[760px] text-left text-sm">
                            <thead className="bg-[#191919] text-gray-400">
                                <tr>
                                    <th className="p-4 font-medium">Ticket ID</th>
                                    <th className="p-4 font-medium">Passenger</th>
                                    <th className="p-4 font-medium">Route</th>
                                    <th className="p-4 font-medium">Amount</th>
                                    <th className="p-4 font-medium">Ticket status</th>
                                    <th className="p-4 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {recentBookings.length > 0 ? (
                                    recentBookings.map((booking) => (
                                        <tr key={booking.id} className="border-t border-gray-800 hover:bg-[#191919]">
                                            <td className="p-4">#{booking.id}</td>
                                            <td className="p-4 font-medium">{booking.passenger}</td>
                                            <td className="p-4">{booking.route}</td>
                                            <td className="p-4">{formatCurrency(booking.amount)}</td>
                                            <td className="p-4">
                                                <span
                                                    className={`rounded-full px-3 py-1 text-xs ${
                                                        bookingStatusClasses[booking.status] ?? 'bg-gray-500/10 text-gray-400'
                                                    }`}
                                                >
                                                    {booking.status}
                                                </span>
                                            </td>
                                            <td className="p-4 whitespace-nowrap">{booking.created_at}</td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="p-8 text-center text-gray-500">
                                            No bookings found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
                <footer className="mt-8 text-center text-xs text-gray-600">Bus Terminal Management System © {new Date().getFullYear()}</footer>
            </main>
        </AppLayout>
    );
}
