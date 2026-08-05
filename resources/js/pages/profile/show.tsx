import { Head, Link, useForm } from '@inertiajs/react';
import { MapPin, Calendar, UserPlus, UserCheck, MessageSquare } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AppLayout } from '@/components/app-shell';

interface User {
    id: number;
    name: string;
    email: string;
    profile_photo_url: string | null;
    profile?: {
        bio: string | null;
        phone: string | null;
        city: string | null;
        country: string | null;
        birth_date: string | null;
        website: string | null;
        facebook_url: string | null;
        twitter_url: string | null;
        instagram_url: string | null;
        is_public: boolean;
    };
}

interface Props {
    profile_user: User;
    is_own: boolean;
    is_friend: boolean;
    friend_id: number | null;
    donations: any[];
    issues: any[];
    stats: {
        donations_count: number;
        issues_count: number;
        friends_count: number;
    };
}

export default function ProfileShow({ profile_user, is_own, is_friend, friend_id, donations, issues, stats }: Props) {
    const { post: sendRequest, processing: sending } = useForm();
    const { post: unfriend, processing: unfriending } = useForm();
    const [friendStatus, setFriendStatus] = useState<string | null>(null);

    const handleSendRequest = () => {
        sendRequest(`/friends/request/${friend_id}`);
        setFriendStatus('pending');
    };

    const handleUnfriend = () => {
        if (confirm('Are you sure you want to unfriend this person?')) {
            unfriend(`/friends/${friend_id}`);
            setFriendStatus(null);
        }
    };

    return (
        <AppLayout>
            <Head title={`${profile_user.name}'s Profile`} />

            <div className="space-y-6">
                {/* Profile Header */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col md:flex-row gap-6">
                            {/* Avatar */}
                            <div className="flex-shrink-0">
                                {profile_user.profile_photo_url ? (
                                    <img
                                        src={profile_user.profile_photo_url}
                                        alt={profile_user.name}
                                        className="w-32 h-32 rounded-full object-cover"
                                    />
                                ) : (
                                    <div className="w-32 h-32 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span className="text-4xl font-medium">
                                            {profile_user.name.charAt(0)}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Info */}
                            <div className="flex-1">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <h1 className="text-2xl font-bold">{profile_user.name}</h1>
                                    </div>
                                    {!is_own && (
                                        <div className="flex gap-2">
                                            {friendStatus === 'accepted' || is_friend ? (
                                                <>
                                                    <Link href={`/messages/${profile_user.id}`}>
                                                        <Button variant="outline">
                                                            <MessageSquare className="mr-2 h-4 w-4" />
                                                            Message
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        variant="outline"
                                                        onClick={handleUnfriend}
                                                        disabled={unfriending}
                                                    >
                                                        Unfriend
                                                    </Button>
                                                </>
                                            ) : friendStatus === 'pending' ? (
                                                <Button variant="outline" disabled>
                                                    <UserCheck className="mr-2 h-4 w-4" />
                                                    Request Sent
                                                </Button>
                                            ) : (
                                                <Button onClick={handleSendRequest} disabled={sending}>
                                                    <UserPlus className="mr-2 h-4 w-4" />
                                                    Add Friend
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                    {is_own && (
                                        <Link href="/profile/me">
                                            <Button variant="outline">Edit Profile</Button>
                                        </Link>
                                    )}
                                </div>

                                {profile_user.profile?.bio && (
                                    <p className="mt-4">{profile_user.profile.bio}</p>
                                )}

                                <div className="flex flex-wrap gap-4 mt-4 text-sm text-muted-foreground">
                                    {profile_user.profile?.city && (
                                        <div className="flex items-center gap-1">
                                            <MapPin className="h-4 w-4" />
                                            {profile_user.profile.city}
                                        </div>
                                    )}
                                    {profile_user.profile?.birth_date && (
                                        <div className="flex items-center gap-1">
                                            <Calendar className="h-4 w-4" />
                                            Born {new Date(profile_user.profile.birth_date).toLocaleDateString()}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Stats */}
                <div className="grid grid-cols-3 gap-4">
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <p className="text-2xl font-bold">{stats.donations_count}</p>
                            <p className="text-sm text-muted-foreground">Donations</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <p className="text-2xl font-bold">{stats.issues_count}</p>
                            <p className="text-sm text-muted-foreground">Issues Created</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6 text-center">
                            <p className="text-2xl font-bold">{stats.friends_count}</p>
                            <p className="text-sm text-muted-foreground">Friends</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Activity Tabs */}
                <Tabs defaultValue="donations" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="donations">Donations</TabsTrigger>
                        <TabsTrigger value="issues">Issues Created</TabsTrigger>
                    </TabsList>

                    <TabsContent value="donations">
                        <Card>
                            <CardHeader>
                                <CardTitle>Donations</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {donations.data.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">
                                        No donations yet
                                    </p>
                                ) : (
                                    <div className="space-y-4">
                                        {donations.data.map((donation: any) => (
                                            <div key={donation.id} className="flex items-center gap-4 p-4 border rounded-lg">
                                                <div className="flex-1">
                                                    <p className="font-semibold text-green-600">{donation.amount}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        To: {donation.donation_issue?.title || 'Unknown'}
                                                    </p>
                                                </div>
                                                <Badge>{donation.status}</Badge>
                                                <span className="text-sm text-muted-foreground">
                                                    {new Date(donation.created_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="issues">
                        <Card>
                            <CardHeader>
                                <CardTitle>Issues Created</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {issues.data.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">
                                        No issues created yet
                                    </p>
                                ) : (
                                    <div className="space-y-4">
                                        {issues.data.map((issue: any) => (
                                            <Link key={issue.id} href={`/issues/${issue.id}`}>
                                                <div className="flex items-center gap-4 p-4 border rounded-lg hover:bg-muted/50 transition-colors cursor-pointer">
                                                    <div className="flex-1">
                                                        <p className="font-medium">{issue.title}</p>
                                                        <p className="text-sm text-muted-foreground line-clamp-1">
                                                            {issue.description}
                                                        </p>
                                                    </div>
                                                    <Badge>{issue.status}</Badge>
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}